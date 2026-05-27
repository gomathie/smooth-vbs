<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Pulls vehicle positions from a Traccar server via its REST API.
 * Docs: https://www.traccar.org/api-reference/
 */
class TraccarDriver implements GpsDriverInterface
{
    public function __construct(private readonly GpsIntegration $integration) {}

    public function fetchVehicleLocations(): array
    {
        $baseUrl = rtrim($this->integration->base_url, '/');

        $response = Http::withBasicAuth(
            $this->integration->username,
            $this->integration->encrypted_password,
        )
        ->timeout(15)
        ->get("{$baseUrl}/api/positions");

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Traccar API returned {$response->status()}: {$response->body()}"
            );
        }

        $locations = [];

        foreach ($response->json() as $position) {
            $deviceId = (string) ($position['deviceId'] ?? null);

            if (! $deviceId || ! isset($position['latitude'], $position['longitude'])) {
                continue;
            }

            $locations[$deviceId] = [
                'gps_vehicle_id' => $deviceId,
                'latitude'       => (float) $position['latitude'],
                'longitude'      => (float) $position['longitude'],
                'recorded_at'    => Carbon::parse($position['fixTime'] ?? $position['serverTime'] ?? 'now'),
            ];
        }

        return $locations;
    }
}
