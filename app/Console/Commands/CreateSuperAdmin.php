<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create a new super admin account';

    public function handle(): int
    {
        $this->info('Create Super Admin');
        $this->line('─────────────────');

        $name = $this->ask('Full name');
        if (! $name) {
            $this->error('Name is required.');
            return self::FAILURE;
        }

        $email = $this->ask('Email address');
        $emailValidation = Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);
        if ($emailValidation->fails()) {
            $this->error($emailValidation->errors()->first('email'));
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 characters)');
        if (! $password || strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $confirm = $this->secret('Confirm password');
        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        // Super admins need an organization. Use the first one, or create a default.
        $organization = Organization::first();
        if (! $organization) {
            $organization = Organization::create([
                'name'     => 'Platform Headquarters',
                'slug'     => 'platform-hq',
                'timezone' => 'UTC',
            ]);
            $this->line("Created default organization: \"{$organization->name}\"");
        }

        $user = User::create([
            'organization_id' => $organization->id,
            'name'            => $name,
            'email'           => $email,
            'password'        => Hash::make($password),
            'role'            => User::ROLE_SUPER_ADMIN,
            'is_active'       => true,
        ]);

        $this->newLine();
        $this->info("Super admin created successfully.");
        $this->table(
            ['Field', 'Value'],
            [
                ['Name',         $user->name],
                ['Email',        $user->email],
                ['Role',         $user->role],
                ['Organization', $organization->name],
            ]
        );

        return self::SUCCESS;
    }
}
