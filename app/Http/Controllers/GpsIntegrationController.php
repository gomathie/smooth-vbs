<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GpsIntegration;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GpsIntegrationController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization_id;

        $integrations = GpsIntegration::where('organization_id', $organizationId)
            ->latest()
            ->get();

        return view('gps.index', compact('integrations'));
    }

    public function create()
    {
        return view('gps.create', ['providers' => GpsIntegration::PROVIDERS]);
    }

    public function store(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $data = $request->validate([
            'label'    => ['required', 'string', 'max:100'],
            'provider' => ['required', 'in:' . implode(',', array_keys(GpsIntegration::PROVIDERS))],
            'base_url' => ['nullable', 'url', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:1'],
        ]);

        $integration = GpsIntegration::create([
            'organization_id'    => $organizationId,
            'label'              => $data['label'],
            'provider'           => $data['provider'],
            'base_url'           => $data['base_url'] ?? null,
            'username'           => $data['username'],
            'encrypted_password' => $data['password'],
            'status'             => GpsIntegration::STATUS_ACTIVE,
        ]);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id'         => Auth::id(),
            'event'           => 'gps_integration_created',
            'auditable_type'  => GpsIntegration::class,
            'auditable_id'    => $integration->id,
            'metadata'        => ['provider' => $integration->provider, 'label' => $integration->label],
        ]);

        return redirect()->route('gps.index')->with('success', 'GPS integration added successfully.');
    }

    public function edit(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $integration    = GpsIntegration::where('organization_id', $organizationId)->findOrFail($id);

        return view('gps.edit', ['integration' => $integration, 'providers' => GpsIntegration::PROVIDERS]);
    }

    public function update(Request $request, string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $integration    = GpsIntegration::where('organization_id', $organizationId)->findOrFail($id);

        $data = $request->validate([
            'label'    => ['required', 'string', 'max:100'],
            'provider' => ['required', 'in:' . implode(',', array_keys(GpsIntegration::PROVIDERS))],
            'base_url' => ['nullable', 'url', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:1'],
            'status'   => ['required', 'in:' . implode(',', [GpsIntegration::STATUS_ACTIVE, GpsIntegration::STATUS_INACTIVE])],
        ]);

        $update = [
            'label'    => $data['label'],
            'provider' => $data['provider'],
            'base_url' => $data['base_url'] ?? null,
            'username' => $data['username'],
            'status'   => $data['status'],
        ];

        // Only update password if a new one was provided.
        if (! empty($data['password'])) {
            $update['encrypted_password'] = $data['password'];
        }

        $integration->update($update);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id'         => Auth::id(),
            'event'           => 'gps_integration_updated',
            'auditable_type'  => GpsIntegration::class,
            'auditable_id'    => $integration->id,
            'metadata'        => ['provider' => $integration->provider, 'status' => $integration->status],
        ]);

        return redirect()->route('gps.index')->with('success', 'GPS integration updated.');
    }

    public function destroy(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $integration    = GpsIntegration::where('organization_id', $organizationId)->findOrFail($id);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id'         => Auth::id(),
            'event'           => 'gps_integration_deleted',
            'auditable_type'  => GpsIntegration::class,
            'auditable_id'    => $integration->id,
            'metadata'        => ['label' => $integration->label],
        ]);

        $integration->delete();

        return redirect()->route('gps.index')->with('success', 'GPS integration removed.');
    }

    public function map()
    {
        $organizationId = Auth::user()->organization_id;

        $vehicles = Vehicle::where('organization_id', $organizationId)
            ->whereNotNull('last_latitude')
            ->whereNotNull('last_longitude')
            ->orderBy('registration_number')
            ->get(['id', 'registration_number', 'vehicle_type', 'status', 'last_latitude', 'last_longitude', 'last_location_at']);

        return view('gps.map', compact('vehicles'));
    }
}
