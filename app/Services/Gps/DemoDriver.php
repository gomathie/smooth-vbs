<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * Returns deterministic fake GPS positions for vehicles in the organization.
 * Used for development, demos, and integration testing without a real GPS server.
 */
class DemoDriver implements GpsDriverInterface
{
    public function __construct(private readonly GpsIntegration $integration) {}

    public function fetchVehicleLocations(): array
    {
        // Nairobi city center as origin; drift vehicles slightly per vehicle ID.
        $baseLat = -1.286389;
        $baseLng = 36.817223;

        $vehicles = Vehicle::where('organization_id', $this->integration->organization_id)
            ->whereNotNull('gps_vehicle_id')
            ->get();

        $locations = [];

        foreach ($vehicles as $vehicle) {
            // Pseudo-random but stable offset based on vehicle ID + minute bucket.
            $seed    = crc32($vehicle->gps_vehicle_id . floor(time() / 180));
            $latOff  = (($seed % 1000) - 500) / 10000;
            $lngOff  = ((($seed >> 10) % 1000) - 500) / 10000;

            $locations[$vehicle->gps_vehicle_id] = [
                'gps_vehicle_id' => $vehicle->gps_vehicle_id,
                'latitude'       => round($baseLat + $latOff, 7),
                'longitude'      => round($baseLng + $lngOff, 7),
                'recorded_at'    => Carbon::now(),
            ];
        }

        return $locations;
    }
}
