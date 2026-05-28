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
