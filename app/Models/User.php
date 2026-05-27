<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'organization_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_FLEET_MANAGER = 'fleet_manager';
    public const ROLE_ORGANIZATION_ADMIN = 'organization_admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ORGANIZATION_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    public static function roles(): array
    {
        return [
            self::ROLE_EMPLOYEE,
            self::ROLE_SUPERVISOR,
            self::ROLE_FLEET_MANAGER,
            self::ROLE_ORGANIZATION_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ];
    }
}
