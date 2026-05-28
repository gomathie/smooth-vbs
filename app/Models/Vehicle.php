<?php

namespace App\Models;

use App\Models\VehicleSensorReading;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'registration_number', 'vehicle_type', 'capacity', 'fuel_type', 'driver_name', 'status', 'gps_vehicle_id', 'imei', 'vin', 'last_latitude', 'last_longitude', 'last_location_at'])]
class Vehicle extends Model
{
    protected $casts = [
        'capacity'         => 'integer',
        'last_latitude'    => 'float',
        'last_longitude'   => 'float',
        'last_location_at' => 'datetime',
    ];

    public function hasLocation(): bool
    {
        return $this->last_latitude !== null && $this->last_longitude !== null;
    }

    public static function normalizeType(string $raw): string
    {
        $t = strtolower(trim($raw));

        return match (true) {
            in_array($t, ['car', 'sedan', 'saloon', 'hatchback', 'coupe', 'automobile', 'compact'])          => 'Car',
            in_array($t, ['truck', 'lorry', 'tipper', 'heavy truck', 'heavy vehicle', 'dump truck', 'tanker'])=> 'Truck',
            in_array($t, ['bike', 'motorcycle', 'motorbike', 'moto', 'scooter', 'bicycle', 'quad'])           => 'Bike',
            in_array($t, ['pickup', 'pickup truck', 'pick-up', 'pick up', 'bakkie'])                          => 'Pickup',
            in_array($t, ['van', 'minivan', 'cargo van', 'panel van', 'delivery van'])                        => 'Van',
            in_array($t, ['bus', 'coach', 'minibus', 'school bus', 'shuttle', 'microbus'])                    => 'Bus',
            in_array($t, ['suv', '4x4', 'jeep', 'offroad', 'off-road', 'crossover', '4wd', 'land cruiser'])  => 'SUV',
            in_array($t, ['trailer', 'semi', 'semi-trailer', 'articulated', 'flatbed', 'curtainsider'])       => 'Trailer',
            in_array($t, ['payloader', 'loader', 'bulldozer', 'excavator', 'grader', 'dozer', 'crane', 'construction', 'forklift']) => 'Payloader',
            default => ucfirst($raw) ?: 'Vehicle',
        };
    }

    public function isGpsOffline(): bool
    {
        if (! $this->gps_vehicle_id) {
            return false;
        }
        return ! $this->last_location_at
            || $this->last_location_at->lt(now()->subMinutes(30));
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sensorReadings()
    {
        return $this->hasMany(VehicleSensorReading::class);
    }
}
