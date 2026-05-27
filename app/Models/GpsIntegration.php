<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['organization_id', 'provider', 'username', 'encrypted_password', 'status', 'last_sync_at'])]
class GpsIntegration extends Model
{
    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
