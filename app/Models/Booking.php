<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['organization_id', 'vehicle_id', 'user_id', 'start_datetime', 'end_datetime', 'purpose', 'destination', 'passenger_count', 'status'])]
class Booking extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_CANCELLED = 'Cancelled';
    public const STATUS_COMPLETED = 'Completed';

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'passenger_count' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(BookingApproval::class);
    }

    public static function conflictExists(int $organizationId, int $vehicleId, string $start, string $end, int $excludeId = null): bool
    {
        $query = self::where('organization_id', $organizationId)
            ->where('vehicle_id', $vehicleId)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);

        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->exists();
    }
}
