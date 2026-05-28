<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::create([
            'name' => 'Smooth VBS Headquarters',
            'slug' => 'smooth-vbs',
            'timezone' => 'UTC',
        ]);

        User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@svbs.com',
            'password' => Hash::make('+233.Svbs2026'),
            'role' => User::ROLE_SUPER_ADMIN,
            'organization_id' => $organization->id,
        ]);
    }
}
