<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class PilotTelematicsDriver implements GpsDriverInterface
{
    public function __construct(private readonly GpsIntegration $integration) {}

    public function fetchVehicleLocations(): array
    {
        $baseUrl = rtrim($this->integration->base_url, '/');

        $response = Http::withBasicAuth(
            $this->integration->username,
            $this->integration->encrypted_password,
        )
        ->withoutVerifying()
        ->timeout(15)
        ->get("{$baseUrl}/api/api.php", [
            'cmd'  => 'list',
            'node' => (int) ($this->integration->config['node'] ?? 1),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Pilot Telematics API returned {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        if (($data['code'] ?? -1) !== 0) {
            throw new \RuntimeException(
                "Pilot Telematics API error: " . ($data['msg'] ?? 'Unknown error')
            );
        }

        $locations = [];

        foreach ($data['list'] ?? [] as $vehicle) {
            $agentId = (string) ($vehicle['agentid'] ?? null);
            $status  = $vehicle['status'] ?? [];

            if (! $agentId || ! isset($status['lat'], $status['lon'])) {
                continue;
            }

            // Skip vehicles with no valid fix (lat and lon are both 0.0).
            if ((float) $status['lat'] === 0.0 && (float) $status['lon'] === 0.0) {
                continue;
            }

            $locations[$agentId] = [
                'gps_vehicle_id' => $agentId,
                'latitude'       => (float) $status['lat'],
                'longitude'      => (float) $status['lon'],
                'recorded_at'    => Carbon::createFromTimestamp((int) $status['unixtimestamp']),
            ];
        }

        return $locations;
    }
}
