<?php

namespace App\Services\Gps;

use App\Models\Vehicle;
use App\Models\VehicleSensorReading;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GpsLocationSyncer
{
    public function __construct(private readonly int $organizationId) {}

    public function applyLocations(array $locations): int
    {
        $updated = 0;

        foreach ($locations as $gpsVehicleId => $loc) {
            $vehicle = null;

            if (! empty($loc['registration_number'])) {
                $vehicle = $this->findVehicleByRegistrationNumber($loc['registration_number']);
            }

            if (! $vehicle) {
                $vehicle = Vehicle::where('organization_id', $this->organizationId)
                    ->where('gps_vehicle_id', $gpsVehicleId)
                    ->first();
            }

            if ($vehicle) {
                $vehicle->update([
                    'last_latitude'       => $loc['latitude'],
                    'last_longitude'      => $loc['longitude'],
                    'last_location_at'    => $loc['recorded_at'],
                    'registration_number' => $loc['registration_number'] ?? $vehicle->registration_number,
                    'vehicle_type'        => isset($loc['vehicle_type']) ? Vehicle::normalizeType($loc['vehicle_type']) : $vehicle->vehicle_type,
                    'status'              => $loc['status'] ?? $vehicle->status,
                    'driver_name'         => $loc['driver_name'] ?? $vehicle->driver_name,
                    'imei'                => $loc['imei'] ?? $vehicle->imei,
                    'vin'                 => $loc['vin'] ?? $vehicle->vin,
                    'gps_vehicle_id'      => $gpsVehicleId,
                ]);

                $updated++;
            } else {
                $vehicle = $this->createVehicleFromLocation($gpsVehicleId, $loc);
                if ($vehicle) {
                    $updated++;
                }
            }

            if (! empty($loc['sensors']) && $vehicle) {
                $this->syncSensorReadings($vehicle, $loc['sensors']);
            }
        }

        return $updated;
    }

    private function createVehicleFromLocation(string $gpsVehicleId, array $loc): ?Vehicle
    {
        $registrationNumber = $loc['registration_number'] ?? $this->buildRegistrationNumber($gpsVehicleId);

        return Vehicle::create([
            'organization_id'  => $this->organizationId,
            'registration_number' => $registrationNumber,
            'vehicle_type'     => isset($loc['vehicle_type']) ? Vehicle::normalizeType($loc['vehicle_type']) : 'Vehicle',
            'capacity'         => $loc['capacity'] ?? 1,
            'status'           => $loc['status'] ?? 'Available',
            'driver_name'      => $loc['driver_name'] ?? null,
            'gps_vehicle_id'   => $gpsVehicleId,
            'imei'             => $loc['imei'] ?? null,
            'vin'              => $loc['vin'] ?? null,
            'last_latitude'    => $loc['latitude'],
            'last_longitude'   => $loc['longitude'],
            'last_location_at' => $loc['recorded_at'],
        ]);
    }

    private function syncSensorReadings(Vehicle $vehicle, array $sensors): void
    {
        foreach ($sensors as $sensor) {
            if (empty($sensor['sensor_name']) || ! isset($sensor['raw_value'])) {
                continue;
            }

            VehicleSensorReading::create([
                'organization_id' => $vehicle->organization_id,
                'vehicle_id'      => $vehicle->id,
                'sensor_name'     => $sensor['sensor_name'],
                'raw_value'       => (string) $sensor['raw_value'],
                'human_value'     => $sensor['human_value'] ?? null,
                'recorded_at'     => $sensor['recorded_at'] instanceof \DateTimeInterface
                    ? $sensor['recorded_at']
                    : Carbon::parse($sensor['recorded_at'] ?? now()),
            ]);
        }
    }

    private function findVehicleByRegistrationNumber(string $registrationNumber): ?Vehicle
    {
        $normalized = $this->normalizeRegistrationNumber($registrationNumber);

        $vehicle = Vehicle::where('organization_id', $this->organizationId)
            ->whereRaw('LOWER(registration_number) = ?', [mb_strtolower(trim($registrationNumber))])
            ->first();

        if ($vehicle) {
            return $vehicle;
        }

        return Vehicle::where('organization_id', $this->organizationId)
            ->get()
            ->first(fn (Vehicle $candidate) => $this->normalizeRegistrationNumber($candidate->registration_number) === $normalized);
    }

    private function normalizeRegistrationNumber(string $registrationNumber): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($registrationNumber)));
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
