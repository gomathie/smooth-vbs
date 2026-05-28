<?php

namespace App\Services\Gps;

use App\Models\Vehicle;
use Illuminate\Support\Str;

class GpsLocationSyncer
{
    public function __construct(private readonly int $organizationId) {}

    public function applyLocations(array $locations): int
    {
        $updated = 0;

        foreach ($locations as $gpsVehicleId => $loc) {
            $rows = Vehicle::where('organization_id', $this->organizationId)
                ->where('gps_vehicle_id', $gpsVehicleId)
                ->update([
                    'last_latitude'    => $loc['latitude'],
                    'last_longitude'   => $loc['longitude'],
                    'last_location_at' => $loc['recorded_at'],
                ]);

            if ($rows > 0) {
                $updated += $rows;
                continue;
            }

            if ($this->createVehicleFromLocation($gpsVehicleId, $loc)) {
                $updated++;
            }
        }

        return $updated;
    }

    private function createVehicleFromLocation(string $gpsVehicleId, array $loc): bool
    {
        $registrationNumber = $this->buildRegistrationNumber($gpsVehicleId);

        $vehicle = Vehicle::create([
            'organization_id'  => $this->organizationId,
            'registration_number' => $registrationNumber,
            'vehicle_type'     => 'GPS vehicle',
            'capacity'         => 1,
            'status'           => 'Available',
            'gps_vehicle_id'   => $gpsVehicleId,
            'last_latitude'    => $loc['latitude'],
            'last_longitude'   => $loc['longitude'],
            'last_location_at' => $loc['recorded_at'],
        ]);

        return $vehicle !== null;
    }

    private function buildRegistrationNumber(string $gpsVehicleId): string
    {
        $base = Str::of($gpsVehicleId)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->value();

        if ($base === '') {
            $base = 'GPS';
        }

        $registrationNumber = "GPS-{$base}";
        $suffix = 0;

        while (Vehicle::where('organization_id', $this->organizationId)
            ->where('registration_number', $registrationNumber)
            ->exists()) {
            $suffix++;
            $registrationNumber = "GPS-{$base}-{$suffix}";
        }

        return $registrationNumber;
    }
}
