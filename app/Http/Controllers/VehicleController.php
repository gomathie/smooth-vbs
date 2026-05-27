<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization_id;
        $vehicles = Vehicle::where('organization_id', $organizationId)
            ->orderBy('registration_number')
            ->paginate(12);

        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $request->validate([
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('vehicles')->where(fn ($query) => $query->where('organization_id', $organizationId))],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        Vehicle::create(array_merge($request->only(['registration_number', 'vehicle_type', 'capacity', 'fuel_type', 'driver_name', 'status']), ['organization_id' => $organizationId]));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully.');
    }

    public function edit(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $vehicle = Vehicle::where('organization_id', $organizationId)->findOrFail($id);

        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $vehicle = Vehicle::where('organization_id', $organizationId)->findOrFail($id);

        $request->validate([
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('vehicles')->where(fn ($query) => $query->where('organization_id', $organizationId))->ignore($vehicle->id)],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $vehicle->update($request->only(['registration_number', 'vehicle_type', 'capacity', 'fuel_type', 'driver_name', 'status']));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $vehicle = Vehicle::where('organization_id', $organizationId)->findOrFail($id);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle removed successfully.');
    }
}
