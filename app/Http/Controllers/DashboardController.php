<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;

        $vehiclesCount = Vehicle::where('organization_id', $organizationId)->count();
        $bookingsCount = Booking::where('organization_id', $organizationId)->count();
        $pendingBookings = Booking::where('organization_id', $organizationId)
            ->where('status', Booking::STATUS_PENDING)
            ->count();
        $approvedBookings = Booking::where('organization_id', $organizationId)
            ->where('status', Booking::STATUS_APPROVED)
            ->count();

        $recentBookings = Booking::with(['vehicle', 'user'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('vehiclesCount', 'bookingsCount', 'pendingBookings', 'approvedBookings', 'recentBookings'));
    }
}
