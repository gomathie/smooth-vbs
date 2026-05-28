@extends('layouts.app')

@section('title', 'Vehicles')
@section('page-title', 'Vehicles')

@section('header-actions')
    @if (auth()->user()->canManageVehicles())
        <a href="{{ route('vehicles.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
            </svg>
            Add Vehicle
        </a>
    @endif
@endsection

@section('content')
@php
$statusBarColor = [
    'Available'   => 'bg-green-500',
    'Booked'      => 'bg-blue-500',
    'In Transit'  => 'bg-orange-500',
    'Maintenance' => 'bg-amber-500',
];
@endphp

{{-- Stats bar --}}
@if ($vehicles->total() > 0)
<div class="mb-6 flex flex-wrap items-center gap-2">
    @foreach ($statusCounts as $status => $count)
        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-xs font-semibold shadow-xs ring-1 ring-slate-200">
            <span class="h-2 w-2 rounded-full {{ $statusBarColor[$status] ?? 'bg-slate-400' }}"></span>
            {{ $count }} {{ $status }}
        </span>
    @endforeach
    @if ($offlineCount > 0)
        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3.5 py-1.5 text-xs font-semibold ring-1 ring-red-200 text-red-700">
            <span class="h-2 w-2 rounded-full bg-red-500"></span>
            {{ $offlineCount }} GPS Offline
        </span>
    @endif
    <span class="ml-auto text-xs text-slate-400">{{ $vehicles->total() }} vehicle{{ $vehicles->total() === 1 ? '' : 's' }} total</span>
</div>
@endif

@if ($vehicles->isEmpty())
    <div class="card">
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            <p class="text-sm text-slate-400">No vehicles registered yet.</p>
            @if (auth()->user()->canManageVehicles())
                <a href="{{ route('vehicles.create') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">Add your first vehicle →</a>
            @endif
        </div>
    </div>
@else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($vehicles as $vehicle)
            @php
                $isOffline = $vehicle->isGpsOffline();
                $barColor  = $statusBarColor[$vehicle->status] ?? 'bg-slate-400';
                $sensors   = $vehicle->sensorReadings->unique('sensor_name')->values();
                $vData = [
                    'registration_number' => $vehicle->registration_number,
                    'vehicle_type'        => $vehicle->vehicle_type,
                    'capacity'            => $vehicle->capacity,
                    'fuel_type'           => $vehicle->fuel_type,
                    'driver_name'         => $vehicle->driver_name,
                    'status'              => $vehicle->status,
                    'gps_vehicle_id'      => $vehicle->gps_vehicle_id,
                    'imei'                => $vehicle->imei,
                    'vin'                 => $vehicle->vin,
                    'last_latitude'       => $vehicle->last_latitude,
                    'last_longitude'      => $vehicle->last_longitude,
                    'last_location_human' => $vehicle->last_location_at?->diffForHumans(),
                    'is_gps_offline'      => $isOffline,
                    'sensors'             => $sensors->map(fn($s) => [
                        'name'  => $s->sensor_name,
                        'value' => $s->human_value ?? $s->raw_value,
                    ])->toArray(),
                    'edit_url'  => auth()->user()->canManageVehicles() ? route('vehicles.edit', $vehicle) : null,
                    'can_manage'=> auth()->user()->canManageVehicles(),
                ];
            @endphp

            <div
                class="relative flex flex-col rounded-xl bg-white ring-1 ring-slate-200 shadow-xs cursor-pointer
                       transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 group"
                data-vehicle="{{ json_encode($vData) }}"
                onclick="openVehicleDrawer(this)"
            >
                {{-- Status accent bar --}}
                <div class="h-1.5 w-full rounded-t-xl {{ $barColor }}"></div>

                <div class="flex flex-1 flex-col p-4">

                    {{-- Header: reg + status badge --}}
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-base font-bold tracking-wide text-slate-900">
                                {{ $vehicle->registration_number }}
                            </p>
                            <p class="text-xs text-slate-500 capitalize">{{ $vehicle->vehicle_type }}</p>
                        </div>
                        <x-status-badge :status="$vehicle->status"/>
                    </div>

                    {{-- Key info --}}
                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            <span class="truncate">{{ $vehicle->driver_name ?? 'No driver assigned' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                            </svg>
                            <span>{{ $vehicle->capacity }} seat{{ $vehicle->capacity === 1 ? '' : 's' }}{{ $vehicle->fuel_type ? ' · ' . $vehicle->fuel_type : '' }}</span>
                        </div>
                    </div>

                    {{-- Sensor badges --}}
                    @if ($sensors->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($sensors->take(3) as $sensor)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                    {{ $sensor->sensor_name }}: {{ $sensor->human_value ?? $sensor->raw_value }}
                                </span>
                            @endforeach
                            @if ($sensors->count() > 3)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-400">
                                    +{{ $sensors->count() - 3 }} more
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Footer: GPS indicator + actions --}}
                    <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3">
                        @if ($vehicle->gps_vehicle_id)
                            @if ($isOffline)
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-red-600">
                                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    GPS Offline
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-green-600">
                                    <span class="inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"></span>
                                    Live
                                </span>
                            @endif
                        @else
                            <span class="text-[10px] text-slate-400">No GPS</span>
                        @endif

                        @if (auth()->user()->canManageVehicles())
                            <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                                <a href="{{ route('vehicles.edit', $vehicle) }}"
                                   class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}"
                                      data-confirm="Delete {{ $vehicle->registration_number }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Hover tooltip (only when enabled in org settings) --}}
                @if ($tooltipsEnabled)
                    <div class="pointer-events-none absolute left-0 right-0 top-full z-40 mt-1 hidden rounded-xl bg-slate-900 p-4 shadow-2xl group-hover:block">
                        <p class="mb-2 text-xs font-semibold text-white">Details</p>
                        <div class="space-y-1.5 text-[11px]">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-400">Type</span>
                                <span class="text-white">{{ $vehicle->vehicle_type }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-400">Capacity</span>
                                <span class="text-white">{{ $vehicle->capacity }} seats</span>
                            </div>
                            @if ($vehicle->fuel_type)
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-400">Fuel</span>
                                    <span class="text-white">{{ $vehicle->fuel_type }}</span>
                                </div>
                            @endif
                            @if ($vehicle->gps_vehicle_id)
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-400">GPS ID</span>
                                    <span class="font-mono text-white">{{ $vehicle->gps_vehicle_id }}</span>
                                </div>
                            @endif
                            @if ($vehicle->last_latitude !== null)
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-400">Location</span>
                                    <span class="font-mono text-white">{{ number_format($vehicle->last_latitude, 4) }}, {{ number_format($vehicle->last_longitude, 4) }}</span>
                                </div>
                            @endif
                            @if ($vehicle->last_location_at)
                                <div class="flex justify-between gap-4">
                                    <span class="text-slate-400">Updated</span>
                                    <span class="text-white">{{ $vehicle->last_location_at->diffForHumans() }}</span>
                                </div>
                            @endif
                        </div>
                        @if ($sensors->isNotEmpty())
                            <div class="mt-3 border-t border-slate-700 pt-3">
                                <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Sensors</p>
                                <div class="space-y-1">
                                    @foreach ($sensors as $sensor)
                                        <div class="flex justify-between gap-4 text-[11px]">
                                            <span class="text-slate-400">{{ $sensor->sensor_name }}</span>
                                            <span class="text-white">{{ $sensor->human_value ?? $sensor->raw_value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if ($vehicles->hasPages())
        <div class="mt-6">{{ $vehicles->links() }}</div>
    @endif
@endif

{{-- Vehicle Detail Drawer --}}
<div id="vehicle-drawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeVehicleDrawer()"></div>
    <div id="drawer-panel"
         class="absolute inset-y-0 right-0 w-full max-w-md overflow-y-auto bg-white shadow-2xl transition-transform duration-300 translate-x-full">
        <div id="drawer-content"></div>
    </div>
</div>

@push('scripts')
<script>
const STATUS_BADGE = {
    'Available':   'bg-green-100 text-green-800 ring-green-300/60',
    'Booked':      'bg-blue-100 text-blue-800 ring-blue-300/60',
    'In Transit':  'bg-orange-100 text-orange-800 ring-orange-300/60',
    'Maintenance': 'bg-amber-100 text-amber-800 ring-amber-300/60',
    'Offline':     'bg-red-100 text-red-800 ring-red-300/60',
};
const STATUS_BAR = {
    'Available':   'bg-green-500',
    'Booked':      'bg-blue-500',
    'In Transit':  'bg-orange-500',
    'Maintenance': 'bg-amber-500',
};

function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function openVehicleDrawer(el) {
    const v = JSON.parse(el.dataset.vehicle);

    const badgeClass = STATUS_BADGE[v.status] ?? 'bg-slate-100 text-slate-600 ring-slate-300/60';
    const barClass   = STATUS_BAR[v.status] ?? 'bg-slate-400';

    const gpsHtml = v.gps_vehicle_id
        ? (v.is_gps_offline
            ? `<span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200">
                   <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>GPS Offline
               </span>`
            : `<span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200">
                   <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-green-500"></span>Live
               </span>`)
        : '';

    const detailRow = (label, value, mono = false) =>
        value ? `<div class="flex items-start justify-between gap-4 py-2 border-b border-slate-100 last:border-0">
                     <span class="shrink-0 text-slate-500">${esc(label)}</span>
                     <span class="${mono ? 'font-mono text-xs' : ''} text-right font-medium text-slate-900">${esc(value)}</span>
                 </div>` : '';

    const locationStr = (v.last_latitude !== null && v.last_longitude !== null)
        ? `${parseFloat(v.last_latitude).toFixed(5)}, ${parseFloat(v.last_longitude).toFixed(5)}`
        : null;

    const sensorsHtml = v.sensors && v.sensors.length
        ? `<div class="px-6 py-4">
               <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Sensor Readings</p>
               <div class="divide-y divide-slate-100 rounded-xl bg-slate-50 px-4">
                   ${v.sensors.map(s => `
                       <div class="flex items-center justify-between py-2.5 text-sm">
                           <span class="text-slate-600">${esc(s.name)}</span>
                           <span class="font-semibold text-slate-900">${esc(s.value)}</span>
                       </div>`).join('')}
               </div>
           </div>`
        : '';

    const actionsHtml = v.can_manage && v.edit_url
        ? `<div class="border-t border-slate-100 px-6 py-4">
               <a href="${esc(v.edit_url)}"
                  class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50">
                   Edit Vehicle
               </a>
           </div>`
        : '';

    document.getElementById('drawer-content').innerHTML = `
        <div class="h-1.5 w-full ${barClass} rounded-t-none"></div>

        <div class="flex items-start justify-between px-6 py-5">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">${esc(v.registration_number)}</h2>
                <p class="mt-0.5 text-sm capitalize text-slate-500">${esc(v.vehicle_type)}</p>
            </div>
            <button onclick="closeVehicleDrawer()"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-2 px-6 pb-4">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ${badgeClass}">
                ${esc(v.status)}
            </span>
            ${gpsHtml}
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Details</p>
            <div class="text-sm">
                ${detailRow('Driver', v.driver_name || 'Not assigned')}
                ${detailRow('Capacity', v.capacity + (v.capacity === 1 ? ' seat' : ' seats'))}
                ${detailRow('Fuel type', v.fuel_type)}
                ${detailRow('GPS Vehicle ID', v.gps_vehicle_id, true)}
                ${detailRow('IMEI', v.imei, true)}
                ${detailRow('VIN', v.vin, true)}
                ${detailRow('Location', locationStr, true)}
                ${detailRow('Last GPS update', v.last_location_human)}
            </div>
        </div>

        ${sensorsHtml}
        ${actionsHtml}
    `;

    const drawer = document.getElementById('vehicle-drawer');
    const panel  = document.getElementById('drawer-panel');
    drawer.classList.remove('hidden');
    requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
}

function closeVehicleDrawer() {
    const panel = document.getElementById('drawer-panel');
    panel.classList.add('translate-x-full');
    setTimeout(() => document.getElementById('vehicle-drawer').classList.add('hidden'), 300);
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeVehicleDrawer(); });
</script>
@endpush
@endsection
