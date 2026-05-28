<?php

namespace App\Models;

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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
