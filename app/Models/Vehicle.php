<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'registration_number', 'vehicle_type', 'capacity', 'fuel_type', 'driver_name', 'status', 'gps_vehicle_id', 'imei', 'vin'])]
class Vehicle extends Model
{
    protected $casts = [
        'capacity' => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
