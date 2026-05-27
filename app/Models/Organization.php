<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'timezone', 'settings'])]
class Organization extends Model
{
    protected $casts = [
        'settings' => AsArrayObject::class,
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function gpsIntegrations()
    {
        return $this->hasMany(GpsIntegration::class);
    }
}
