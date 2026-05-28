<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization_id;
        $now            = Carbon::now();
        $monthStart     = $now->copy()->startOfMonth();

        $stats = [
            'total'     => Booking::where('organization_id', $organizationId)->count(),
            'pending'   => Booking::where('organization_id', $organizationId)->where('status', Booking::STATUS_PENDING)->count(),
            'approved'  => Booking::where('organization_id', $organizationId)->where('status', Booking::STATUS_APPROVED)->count(),
            'rejected'  => Booking::where('organization_id', $organizationId)->where('status', Booking::STATUS_REJECTED)->count(),
            'cancelled' => Booking::where('organization_id', $organizationId)->where('status', Booking::STATUS_CANCELLED)->count(),
            'completed' => Booking::where('organization_id', $organizationId)->where('status', Booking::STATUS_COMPLETED)->count(),
            'this_month' => Booking::where('organization_id', $organizationId)
                ->where('start_datetime', '>=', $monthStart)
                ->count(),
        ];

        // Bookings per month for the last 6 months.
        $monthly = Booking::where('organization_id', $organizationId)
            ->where('start_datetime', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(start_datetime, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Fill in any missing months with zero.
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $key          = $now->copy()->subMonths($i)->format('Y-m');
            $months[$key] = $monthly[$key] ?? 0;
        }

        return view('reports.index', compact('stats', 'months'));
    }

    public function bookings(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $from    = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to      = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::now()->endOfDay();
        $status  = $request->input('status');
        $vehicle = $request->input('vehicle_id');

        $query = Booking::with(['vehicle', 'user'])
            ->where('organization_id', $organizationId)
            ->whereBetween('start_datetime', [$from, $to]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($vehicle) {
            $query->where('vehicle_id', $vehicle);
        }

        $vehicles = Vehicle::where('organization_id', $organizationId)->orderBy('registration_number')->get();

        // CSV export
        if ($request->boolean('export')) {
            return $this->exportBookingsCsv($query->orderBy('start_datetime')->get());
        }

        $bookings = $query->orderBy('start_datetime', 'desc')->paginate(20)->withQueryString();

        return view('reports.bookings', compact('bookings', 'vehicles', 'from', 'to', 'status', 'vehicle'));
    }

    public function utilization(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to   = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::now()->endOfDay();

        $periodHours = $from->diffInHours($to) ?: 1;

        // Aggregate per vehicle.
        $rows = Booking::where('organization_id', $organizationId)
            ->whereBetween('start_datetime', [$from, $to])
            ->whereIn('status', [Booking::STATUS_APPROVED, Booking::STATUS_COMPLETED])
            ->select(
                'vehicle_id',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, start_datetime, end_datetime)) / 3600 as hours_booked')
            )
            ->groupBy('vehicle_id')
            ->with('vehicle:id,registration_number,vehicle_type,capacity')
            ->get()
            ->map(function ($row) use ($periodHours) {
                $row->hours_booked       = round((float) $row->hours_booked, 1);
                $row->utilization_pct    = min(100, round($row->hours_booked / $periodHours * 100, 1));
                return $row;
            })
            ->sortByDesc('hours_booked');

        // Also include vehicles with zero bookings.
        $bookedVehicleIds = $rows->pluck('vehicle_id');
        $idle = Vehicle::where('organization_id', $organizationId)
            ->whereNotIn('id', $bookedVehicleIds)
            ->orderBy('registration_number')
            ->get()
            ->map(function ($v) {
                return (object) [
                    'vehicle'          => $v,
                    'booking_count'    => 0,
                    'hours_booked'     => 0.0,
                    'utilization_pct'  => 0.0,
                ];
            });

        $utilization = $rows->concat($idle);

        if ($request->boolean('export')) {
            return $this->exportUtilizationCsv($utilization, $from, $to);
        }

        return view('reports.utilization', compact('utilization', 'from', 'to', 'periodHours'));
    }

    private function exportBookingsCsv($bookings)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bookings-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Vehicle', 'Requested By', 'Start', 'End', 'Purpose', 'Destination', 'Passengers', 'Status']);

            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->id,
                    $b->vehicle->registration_number ?? '',
                    $b->user->name ?? '',
                    $b->start_datetime->format('Y-m-d H:i'),
                    $b->end_datetime->format('Y-m-d H:i'),
                    $b->purpose,
                    $b->destination ?? '',
                    $b->passenger_count,
                    $b->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportUtilizationCsv($utilization, Carbon $from, Carbon $to)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="utilization-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($utilization, $from, $to) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Period', $from->format('Y-m-d') . ' to ' . $to->format('Y-m-d')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Registration', 'Type', 'Bookings', 'Hours Booked', 'Utilization %']);

            foreach ($utilization as $row) {
                fputcsv($handle, [
                    $row->vehicle->registration_number ?? '',
                    $row->vehicle->vehicle_type ?? '',
                    $row->booking_count,
                    $row->hours_booked,
                    $row->utilization_pct,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
