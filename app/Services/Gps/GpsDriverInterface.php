<?php

namespace App\Services\Gps;

interface GpsDriverInterface
{
    /**
     * Return an array of vehicle location records keyed by the GPS platform's vehicle ID.
     *
     * Each element must contain:
     *   'gps_vehicle_id' => string
     *   'latitude'       => float
     *   'longitude'      => float
     *   'recorded_at'    => \DateTimeInterface
     *
     * Throw \RuntimeException on connection or auth failures so the caller can
     * mark the integration as errored and log the problem.
     *
     * @return array<string, array{gps_vehicle_id: string, latitude: float, longitude: float, recorded_at: \DateTimeInterface}>
     */
    public function fetchVehicleLocations(): array;
}
