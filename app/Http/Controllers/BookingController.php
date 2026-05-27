<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\BookingApproval;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization_id;

        $bookings = Booking::with(['vehicle', 'user'])
            ->where('organization_id', $organizationId)
            ->latest('start_datetime')
            ->paginate(12);

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        $organizationId = Auth::user()->organization_id;
        $vehicles = Vehicle::where('organization_id', $organizationId)->orderBy('registration_number')->get();

        return view('bookings.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'start_datetime' => ['required', 'date', 'after:now'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'purpose' => ['required', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'passenger_count' => ['required', 'integer', 'min:1'],
        ]);

        $vehicle = Vehicle::where('organization_id', $organizationId)->findOrFail($request->input('vehicle_id'));
        $start = Carbon::parse($request->input('start_datetime'));
        $end = Carbon::parse($request->input('end_datetime'));

        if (Booking::conflictExists($organizationId, $vehicle->id, $start->toDateTimeString(), $end->toDateTimeString())) {
            return back()->withErrors(['vehicle_id' => 'This vehicle already has a conflicting booking for the requested time range.'])->withInput();
        }

        $booking = Booking::create([
            'organization_id' => $organizationId,
            'vehicle_id' => $vehicle->id,
            'user_id' => Auth::id(),
            'start_datetime' => $start,
            'end_datetime' => $end,
            'purpose' => $request->input('purpose'),
            'destination' => $request->input('destination'),
            'passenger_count' => $request->input('passenger_count'),
            'status' => Booking::STATUS_PENDING,
        ]);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id' => Auth::id(),
            'event' => 'booking_submitted',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'metadata' => [
                'vehicle_id' => $vehicle->id,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ],
        ]);

        return redirect()->route('bookings.index')->with('success', 'Booking request submitted and awaiting approval.');
    }

    public function show(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $booking = Booking::with(['vehicle', 'user', 'approvals.approver'])
            ->where('organization_id', $organizationId)
            ->findOrFail($id);

        return view('bookings.show', compact('booking'));
    }

    public function update(Request $request, string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $booking = Booking::where('organization_id', $organizationId)->findOrFail($id);
        $action = $request->input('action');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($action === 'cancel') {
            if ($booking->user_id !== Auth::id() && ! $user->isAdmin()) {
                abort(403);
            }

            $booking->update(['status' => Booking::STATUS_CANCELLED]);

            AuditLog::create([
                'organization_id' => $organizationId,
                'user_id' => Auth::id(),
                'event' => 'booking_cancelled',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'metadata' => ['status' => $booking->status],
            ]);

            return redirect()->route('bookings.index')->with('success', 'Booking cancelled.');
        }

        if (! $user->canApproveBookings()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:' . Booking::STATUS_APPROVED . ',' . Booking::STATUS_REJECTED],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $newStatus = $request->input('status');

        if ($newStatus === Booking::STATUS_APPROVED) {
            $start = $booking->start_datetime->toDateTimeString();
            $end = $booking->end_datetime->toDateTimeString();

            if (Booking::conflictExists($organizationId, $booking->vehicle_id, $start, $end, $booking->id)) {
                return back()->withErrors(['status' => 'This booking conflicts with another approved or pending booking.']);
            }
        }

        DB::transaction(function () use ($booking, $newStatus, $request, $organizationId) {
            $booking->update(['status' => $newStatus]);
            BookingApproval::create([
                'booking_id' => $booking->id,
                'approver_id' => Auth::id(),
                'status' => $newStatus,
                'comment' => $request->input('comment'),
                'acted_at' => Carbon::now(),
            ]);
            AuditLog::create([
                'organization_id' => $organizationId,
                'user_id' => Auth::id(),
                'event' => 'booking_' . strtolower($newStatus),
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'metadata' => ['status' => $newStatus],
            ]);
        });

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking ' . strtolower($newStatus) . '.');
    }

    public function destroy(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $booking = Booking::where('organization_id', $organizationId)->findOrFail($id);

        if ($booking->user_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id' => Auth::id(),
            'event' => 'booking_cancelled',
            'auditable_type' => Booking::class,
            'auditable_id' => $booking->id,
            'metadata' => ['status' => $booking->status],
        ]);

        return redirect()->route('bookings.index')->with('success', 'Booking request cancelled.');
    }
}
