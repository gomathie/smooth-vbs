@extends('layouts.app')

@section('title', 'Vehicles')
@section('page-title', 'Vehicles')

@section('header-actions')
    <a href="{{ route('vehicles.create') }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
        </svg>
        Add Vehicle
    </a>
@endsection

@section('content')
<div class="card">
    @if ($vehicles->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            <p class="text-sm text-slate-400">No vehicles registered yet.</p>
            <a href="{{ route('vehicles.create') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">Add your first vehicle →</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Fuel</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $vehicle->registration_number }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $vehicle->vehicle_type }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $vehicle->capacity }} seats</td>
                            <td class="px-6 py-4 text-slate-600">{{ $vehicle->fuel_type ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $vehicle->driver_name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$vehicle->status"/>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('vehicles.destroy', $vehicle) }}" data-confirm="Delete {{ $vehicle->registration_number }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger py-1.5 px-3 text-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($vehicles->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $vehicles->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
