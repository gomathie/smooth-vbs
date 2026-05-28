<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'vehicle_id', 'sensor_name', 'raw_value', 'human_value', 'recorded_at'])]
class VehicleSensorReading extends Model
{
    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
