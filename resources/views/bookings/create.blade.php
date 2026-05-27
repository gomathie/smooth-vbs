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

                <div>
                    <label for="vehicle_id" class="form-label">Vehicle <span class="text-red-500">*</span></label>
                    <select id="vehicle_id" name="vehicle_id" required class="form-select">
                        <option value="">— Select a vehicle —</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                {{ $vehicle->registration_number }} — {{ $vehicle->vehicle_type }} ({{ $vehicle->capacity }} seats)
                            </option>
                        @endforeach
                    </select>
                </div>

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
