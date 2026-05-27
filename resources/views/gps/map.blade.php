@extends('layouts.app')

@section('title', 'Live Fleet Map')
@section('page-title', 'Live Fleet Map')

@section('header-actions')
    <a href="{{ route('gps.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Integrations
    </a>
    <button onclick="location.reload()" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
        </svg>
        Refresh
    </button>
@endsection

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endpush

@section('content')
<div class="space-y-4">

    {{-- Stats bar --}}
    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
        <span>
            <strong class="text-slate-900">{{ $vehicles->count() }}</strong> vehicle(s) with location data
        </span>
        <span class="text-slate-300">|</span>
        <span>Locations updated every 3 minutes via cron</span>
        @if ($vehicles->isNotEmpty())
            <span class="text-slate-300">|</span>
            <span>Last position: {{ $vehicles->max('last_location_at')?->diffForHumans() ?? '—' }}</span>
        @endif
    </div>

    @if ($vehicles->isEmpty())
        <div class="card flex flex-col items-center justify-center gap-3 py-20 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-12 w-12 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <p class="text-slate-500 font-medium">No vehicle positions available yet.</p>
            <p class="text-sm text-slate-400 max-w-sm">
                Make sure your GPS integrations are active and your vehicles have a
                <strong>GPS Vehicle ID</strong> set. The sync runs every 3 minutes.
            </p>
            <a href="{{ route('gps.index') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                Configure GPS integrations →
            </a>
        </div>
    @else
        {{-- Map container --}}
        <div class="card overflow-hidden p-0">
            <div id="map" style="height: 540px; width: 100%;"></div>
        </div>

        {{-- Vehicle list --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Vehicles on map</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/60">
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Coordinates</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($vehicles as $vehicle)
                            <tr class="hover:bg-slate-50/60 transition-colors cursor-pointer"
                                onclick="focusVehicle({{ $vehicle->last_latitude }}, {{ $vehicle->last_longitude }}, '{{ e($vehicle->registration_number) }}')">
                                <td class="px-6 py-3 font-semibold text-slate-900">{{ $vehicle->registration_number }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $vehicle->vehicle_type }}</td>
                                <td class="px-6 py-3"><x-status-badge :status="$vehicle->status"/></td>
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">
                                    {{ number_format($vehicle->last_latitude, 5) }},
                                    {{ number_format($vehicle->last_longitude, 5) }}
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-400">
                                    {{ $vehicle->last_location_at?->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@if ($vehicles->isNotEmpty())
@push('scripts')
<script>
const vehicles = @json($vehicles->map(fn($v) => [
    'registration_number' => $v->registration_number,
    'vehicle_type'        => $v->vehicle_type,
    'status'              => $v->status,
    'lat'                 => $v->last_latitude,
    'lng'                 => $v->last_longitude,
    'updated_at'          => $v->last_location_at?->diffForHumans(),
]));

const map = L.map('map');

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

const markers = [];

vehicles.forEach(v => {
    const marker = L.marker([v.lat, v.lng])
        .addTo(map)
        .bindPopup(`
            <div class="text-sm">
                <p class="font-bold">${v.registration_number}</p>
                <p class="text-gray-600">${v.vehicle_type} &mdash; ${v.status}</p>
                <p class="text-gray-400 text-xs mt-1">Updated ${v.updated_at ?? '—'}</p>
            </div>
        `);
    markers.push({ marker, registration_number: v.registration_number });
});

if (markers.length > 0) {
    const group = L.featureGroup(markers.map(m => m.marker));
    map.fitBounds(group.getBounds().pad(0.2));
}

function focusVehicle(lat, lng, reg) {
    map.setView([lat, lng], 15);
    const found = markers.find(m => m.registration_number === reg);
    if (found) found.marker.openPopup();
}
</script>
@endpush
@endif
