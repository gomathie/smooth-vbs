@extends('layouts.app')

@section('title', 'Vehicle Utilization')
@section('page-title', 'Vehicle Utilization')

@section('header-actions')
    <a href="{{ route('reports.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Reports
    </a>
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        Export CSV
    </a>
@endsection

@section('content')

{{-- Date range filter --}}
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.utilization') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input">
            </div>
            <button type="submit" class="btn-primary">Apply</button>
            <a href="{{ route('reports.utilization') }}" class="btn-secondary">Reset</a>
        </form>
    </div>
</div>

{{-- Context --}}
<p class="mb-4 text-xs text-slate-400">
    Period: <strong class="text-slate-600">{{ $from->format('d M Y') }}</strong>
    to <strong class="text-slate-600">{{ $to->format('d M Y') }}</strong>
    ({{ round($periodHours) }} available hours).
    Utilization is based on <em>approved</em> and <em>completed</em> bookings only.
</p>

<div class="card">
    @if ($utilization->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <p class="text-sm text-slate-400">No vehicles found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Bookings</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Hours booked</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 w-48">Utilization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($utilization as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-3 font-semibold text-slate-900">
                                {{ $row->vehicle->registration_number ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-slate-600">{{ $row->vehicle->vehicle_type ?? '—' }}</td>
                            <td class="px-6 py-3 text-right text-slate-700">{{ $row->booking_count }}</td>
                            <td class="px-6 py-3 text-right text-slate-700">{{ number_format($row->hours_booked, 1) }} h</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all
                                            {{ $row->utilization_pct >= 75 ? 'bg-green-500' : ($row->utilization_pct >= 30 ? 'bg-blue-500' : 'bg-slate-300') }}"
                                             style="width: {{ $row->utilization_pct }}%">
                                        </div>
                                    </div>
                                    <span class="w-10 text-right text-xs text-slate-500">{{ $row->utilization_pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
