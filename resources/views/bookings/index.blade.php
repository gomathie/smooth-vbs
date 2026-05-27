@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('header-actions')
    <a href="{{ route('bookings.create') }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
        </svg>
        Request Booking
    </a>
@endsection

@section('content')
<div class="card">
    @if ($bookings->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <p class="text-sm text-slate-400">No bookings found.</p>
            <a href="{{ route('bookings.create') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">Create a booking request →</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Requested by</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Start</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">End</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($bookings as $booking)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $booking->vehicle->registration_number }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $booking->start_datetime->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $booking->end_datetime->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-slate-600 max-w-[200px] truncate">{{ $booking->purpose }}</td>
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

        @if ($bookings->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $bookings->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
