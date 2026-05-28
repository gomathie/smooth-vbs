@extends('layouts.app')

@section('title', 'Request Booking')
@section('page-title', 'Request Booking')

@section('header-actions')
    <a href="{{ route('bookings.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">Booking Details</h2>
            <p class="text-xs text-slate-400">Fill in the details for your booking request.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bookings.store') }}" class="space-y-5">
                @csrf

                @php
                $vehicleList = $vehicles->map(fn($v) => [
                    'id'       => $v->id,
                    'reg'      => $v->registration_number,
                    'type'     => $v->vehicle_type,
                    'capacity' => $v->capacity,
                    'status'   => $v->status,
                ])->values();
                $oldVehicleId = old('vehicle_id');
                @endphp

                <div>
                    <label class="form-label">Vehicle <span class="text-red-500">*</span></label>
                    <input type="hidden" id="vehicle_id" name="vehicle_id" value="{{ $oldVehicleId }}" required>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <input
                            id="vehicle-search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search by registration number or name…"
                            class="form-input pl-9"
                        >
                        <ul id="vehicle-dropdown"
                            class="absolute left-0 right-0 top-full z-20 mt-1 hidden max-h-60 overflow-y-auto rounded-xl bg-white py-1 shadow-xl ring-1 ring-slate-200">
                        </ul>
                    </div>

                    {{-- Selected chip --}}
                    <div id="vehicle-selected" class="mt-2 hidden items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm">
                        <span id="vehicle-selected-label" class="font-medium text-blue-900"></span>
                        <button type="button" onclick="clearVehicle()" class="ml-2 text-blue-400 hover:text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <script>
                (function () {
                    const vehicles = @json($vehicleList);
                    const STATUS_COLOR = {
                        'Available':   'bg-green-100 text-green-700',
                        'Booked':      'bg-blue-100 text-blue-700',
                        'In Transit':  'bg-orange-100 text-orange-700',
                        'Maintenance': 'bg-amber-100 text-amber-700',
                    };

                    const searchInput   = document.getElementById('vehicle-search');
                    const dropdown      = document.getElementById('vehicle-dropdown');
                    const hiddenInput   = document.getElementById('vehicle_id');
                    const selectedChip  = document.getElementById('vehicle-selected');
                    const selectedLabel = document.getElementById('vehicle-selected-label');
                    let activeIndex = -1;

                    // Restore old() value on validation failure
                    @if($oldVehicleId)
                    const preselect = vehicles.find(v => v.id == {{ (int)$oldVehicleId }});
                    if (preselect) selectVehicle(preselect);
                    @endif

                    function filtered() {
                        const q = searchInput.value.trim().toLowerCase();
                        if (!q) return vehicles;
                        return vehicles.filter(v =>
                            v.reg.toLowerCase().includes(q) ||
                            String(v.id).includes(q)
                        );
                    }

                    function esc(s) {
                        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                    }

                    function renderDropdown() {
                        const list = filtered();
                        activeIndex = -1;
                        if (!list.length) {
                            dropdown.innerHTML = '<li class="px-4 py-3 text-sm text-slate-400">No vehicles match your search.</li>';
                        } else {
                            dropdown.innerHTML = list.map((v, i) => {
                                const badge = STATUS_COLOR[v.status] ?? 'bg-slate-100 text-slate-600';
                                return `<li data-i="${i}"
                                            class="vehicle-opt flex cursor-pointer items-center justify-between px-4 py-2.5 hover:bg-slate-50"
                                            onmousedown="event.preventDefault()"
                                            onclick="pickFromDropdown(${i})">
                                    <div>
                                        <span class="font-semibold text-slate-900">${esc(v.reg)}</span>
                                        <span class="ml-2 text-xs text-slate-500">${esc(v.type)} · ${v.capacity} seats</span>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium ${badge}">${esc(v.status)}</span>
                                </li>`;
                            }).join('');
                        }
                        dropdown.classList.remove('hidden');
                    }

                    window.pickFromDropdown = function(i) {
                        selectVehicle(filtered()[i]);
                    };

                    function selectVehicle(v) {
                        hiddenInput.value = v.id;
                        searchInput.value = '';
                        selectedLabel.textContent = v.reg + ' — ' + v.type + ' (' + v.capacity + ' seats)';
                        selectedChip.classList.remove('hidden');
                        selectedChip.classList.add('flex');
                        dropdown.classList.add('hidden');
                    }

                    window.clearVehicle = function() {
                        hiddenInput.value = '';
                        selectedChip.classList.add('hidden');
                        selectedChip.classList.remove('flex');
                        searchInput.focus();
                    };

                    searchInput.addEventListener('focus', renderDropdown);
                    searchInput.addEventListener('input', renderDropdown);
                    searchInput.addEventListener('blur', () => setTimeout(() => dropdown.classList.add('hidden'), 150));

                    searchInput.addEventListener('keydown', e => {
                        const opts = dropdown.querySelectorAll('.vehicle-opt');
                        if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = Math.min(activeIndex + 1, opts.length - 1); }
                        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = Math.max(activeIndex - 1, 0); }
                        else if (e.key === 'Enter' && activeIndex >= 0) { e.preventDefault(); opts[activeIndex].click(); return; }
                        else if (e.key === 'Escape') { dropdown.classList.add('hidden'); return; }
                        opts.forEach((el, i) => el.classList.toggle('bg-slate-100', i === activeIndex));
                        if (opts[activeIndex]) opts[activeIndex].scrollIntoView({ block: 'nearest' });
                    });
                })();
                </script>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="start_datetime" class="form-label">Start date & time <span class="text-red-500">*</span></label>
                        <input
                            id="start_datetime"
                            type="datetime-local"
                            name="start_datetime"
                            value="{{ old('start_datetime') }}"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="end_datetime" class="form-label">End date & time <span class="text-red-500">*</span></label>
                        <input
                            id="end_datetime"
                            type="datetime-local"
                            name="end_datetime"
                            value="{{ old('end_datetime') }}"
                            required
                            class="form-input"
                        >
                    </div>
                </div>

                <div>
                    <label for="purpose" class="form-label">Purpose <span class="text-red-500">*</span></label>
                    <input
                        id="purpose"
                        type="text"
                        name="purpose"
                        value="{{ old('purpose') }}"
                        required
                        class="form-input"
                        placeholder="e.g. Client visit, Airport pickup"
                    >
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="destination" class="form-label">Destination</label>
                        <input
                            id="destination"
                            type="text"
                            name="destination"
                            value="{{ old('destination') }}"
                            class="form-input"
                            placeholder="City or address"
                        >
                    </div>

                    <div>
                        <label for="passenger_count" class="form-label">Passengers <span class="text-red-500">*</span></label>
                        <input
                            id="passenger_count"
                            type="number"
                            name="passenger_count"
                            value="{{ old('passenger_count', 1) }}"
                            min="1"
                            required
                            class="form-input"
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <a href="{{ route('bookings.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
