<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'name', 'label', 'unit', 'description', 'is_active'])]
class VehicleSensor extends Model
{
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
