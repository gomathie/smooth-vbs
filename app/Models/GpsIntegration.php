<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

#[Fillable(['organization_id', 'provider', 'label', 'base_url', 'username', 'encrypted_password', 'status', 'last_sync_at'])]
class GpsIntegration extends Model
{
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';
    public const STATUS_ERROR = 'Error';

    public const PROVIDERS = [
        'traccar' => 'Traccar',
        'demo'    => 'Demo (test mode)',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    protected function encryptedPassword(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Crypt::decryptString($value),
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
