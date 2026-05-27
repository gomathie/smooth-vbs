@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('header-actions')
    <span class="text-sm text-slate-400">{{ now()->format('l, d M Y') }}</span>
@endsection

@section('content')

{{-- Stat cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <div class="card p-5">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $vehiclesCount }}</p>
                <p class="text-sm text-slate-500">Total Vehicles</p>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-indigo-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $bookingsCount }}</p>
                <p class="text-sm text-slate-500">Total Bookings</p>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-amber-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $pendingBookings }}</p>
                <p class="text-sm text-slate-500">Pending Approval</p>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-900">{{ $approvedBookings }}</p>
                <p class="text-sm text-slate-500">Approved Bookings</p>
            </div>
        </div>
    </div>

</div>

{{-- Recent bookings --}}
<div class="card mt-6">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-slate-900">Recent Bookings</h2>
        <a href="{{ route('bookings.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View all</a>
    </div>

    @if ($recentBookings->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <p class="text-sm text-slate-400">No bookings yet.</p>
            <a href="{{ route('bookings.create') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">Request the first booking →</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Requested by</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Period</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentBookings as $booking)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $booking->vehicle->registration_number }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $booking->start_datetime->format('d M H:i') }}
                                <span class="text-slate-400">→</span>
                                {{ $booking->end_datetime->format('d M H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$booking->status"/>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('bookings.show', $booking) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
