@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('header-actions')
    <span class="text-sm text-slate-400">{{ now()->format('l, d M Y') }}</span>
@endsection

@section('content')

{{-- Status summary cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">

    @php
        $cards = [
            ['label' => 'Total',     'value' => $stats['total'],     'color' => 'slate'],
            ['label' => 'This Month','value' => $stats['this_month'],'color' => 'blue'],
            ['label' => 'Pending',   'value' => $stats['pending'],   'color' => 'amber'],
            ['label' => 'Approved',  'value' => $stats['approved'],  'color' => 'green'],
            ['label' => 'Rejected',  'value' => $stats['rejected'],  'color' => 'red'],
            ['label' => 'Cancelled', 'value' => $stats['cancelled'], 'color' => 'slate'],
        ];
        $colorMap = [
            'slate' => 'bg-slate-100 text-slate-700',
            'blue'  => 'bg-blue-100 text-blue-700',
            'amber' => 'bg-amber-100 text-amber-700',
            'green' => 'bg-green-100 text-green-700',
            'red'   => 'bg-red-100 text-red-700',
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="card p-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $colorMap[$card['color']] }}">
                <span class="text-sm font-bold">{{ $card['value'] }}</span>
            </div>
            <p class="mt-2 text-xs font-medium text-slate-500">{{ $card['label'] }} Bookings</p>
        </div>
    @endforeach

</div>

{{-- Monthly trend bar chart (CSS-only) --}}
<div class="card mt-6">
    <div class="card-header">
        <h2 class="text-sm font-semibold text-slate-900">Bookings — Last 6 months</h2>
    </div>
    <div class="card-body">
        @php $max = max(array_values($months)) ?: 1; @endphp
        <div class="flex items-end gap-3 h-40">
            @foreach ($months as $month => $count)
                <div class="flex flex-1 flex-col items-center gap-1">
                    <span class="text-xs font-semibold text-slate-700">{{ $count ?: '' }}</span>
                    <div class="w-full rounded-t-md bg-blue-500 transition-all"
                         style="height: {{ $max > 0 ? round($count / $max * 130) : 0 }}px; min-height: {{ $count > 0 ? 4 : 0 }}px;">
                    </div>
                    <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($month . '-01')->format('M') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Quick links to detailed reports --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

    <a href="{{ route('reports.bookings') }}" class="card p-5 hover:border-blue-300 hover:bg-blue-50/30 transition-colors group">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 group-hover:bg-indigo-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-indigo-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Booking History</p>
                <p class="text-sm text-slate-500 mt-0.5">Filter by date, status, vehicle. Export to CSV.</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ml-auto h-5 w-5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
        </div>
    </a>

    <a href="{{ route('reports.utilization') }}" class="card p-5 hover:border-blue-300 hover:bg-blue-50/30 transition-colors group">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-green-100 group-hover:bg-green-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-slate-900">Vehicle Utilization</p>
                <p class="text-sm text-slate-500 mt-0.5">Hours booked per vehicle, utilization %. Export to CSV.</p>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ml-auto h-5 w-5 text-slate-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
        </div>
    </a>

</div>
@endsection
