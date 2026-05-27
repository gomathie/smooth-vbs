@extends('layouts.app')

@section('title', 'Edit Vehicle')
@section('page-title', 'Edit Vehicle')

@section('header-actions')
    <a href="{{ route('vehicles.index') }}" class="btn-secondary">
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
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Vehicle Details</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $vehicle->registration_number }}</p>
            </div>
            <x-status-badge :status="$vehicle->status"/>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="registration_number" class="form-label">Registration number <span class="text-red-500">*</span></label>
                        <input
                            id="registration_number"
                            type="text"
                            name="registration_number"
                            value="{{ old('registration_number', $vehicle->registration_number) }}"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="vehicle_type" class="form-label">Vehicle type <span class="text-red-500">*</span></label>
                        <input
                            id="vehicle_type"
                            type="text"
                            name="vehicle_type"
                            value="{{ old('vehicle_type', $vehicle->vehicle_type) }}"
                            required
                            class="form-input"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="capacity" class="form-label">Passenger capacity <span class="text-red-500">*</span></label>
                        <input
                            id="capacity"
                            type="number"
                            name="capacity"
                            value="{{ old('capacity', $vehicle->capacity) }}"
                            min="1"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="fuel_type" class="form-label">Fuel type</label>
                        <select id="fuel_type" name="fuel_type" class="form-select">
                            <option value="">— Select —</option>
                            @foreach(['Petrol', 'Diesel', 'Electric', 'Hybrid', 'LPG'] as $fuel)
                                <option value="{{ $fuel }}" @selected(old('fuel_type', $vehicle->fuel_type) === $fuel)>{{ $fuel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="driver_name" class="form-label">Assigned driver</label>
                        <input
                            id="driver_name"
                            type="text"
                            name="driver_name"
                            value="{{ old('driver_name', $vehicle->driver_name) }}"
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="form-select">
                            @foreach(['Available', 'Booked', 'In Transit', 'Maintenance', 'Offline'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $vehicle->status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                    <a href="{{ route('vehicles.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
