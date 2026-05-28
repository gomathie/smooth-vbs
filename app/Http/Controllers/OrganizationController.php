<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::withCount(['users', 'vehicles'])
            ->orderBy('name')
            ->paginate(20);

        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('organizations.create', ['timezones' => $this->timezones()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'timezone'       => ['required', Rule::in(array_keys($this->timezones()))],
            'admin_name'     => ['nullable', 'string', 'max:255', 'required_with:admin_email'],
            'admin_email'    => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_with:admin_name'],
            'admin_password' => ['nullable', 'string', 'min:8', 'required_with:admin_email'],
        ]);

        $slug = $this->uniqueSlug($request->input('name'));

        $organization = Organization::create([
            'name'     => $request->input('name'),
            'slug'     => $slug,
            'timezone' => $request->input('timezone'),
        ]);

        AuditLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => 'organization_created',
            'auditable_type'  => Organization::class,
            'auditable_id'    => $organization->id,
            'metadata'        => ['name' => $organization->name],
        ]);

        if ($request->filled('admin_email')) {
            $admin = User::create([
                'organization_id' => $organization->id,
                'name'            => $request->input('admin_name'),
                'email'           => $request->input('admin_email'),
                'password'        => Hash::make($request->input('admin_password')),
                'role'            => User::ROLE_ORGANIZATION_ADMIN,
                'is_active'       => true,
            ]);

            AuditLog::create([
                'organization_id' => Auth::user()->organization_id,
                'user_id'         => Auth::id(),
                'event'           => 'user_created',
                'auditable_type'  => User::class,
                'auditable_id'    => $admin->id,
                'metadata'        => ['role' => $admin->role, 'organization_id' => $organization->id],
            ]);
        }

        return redirect()->route('organizations.index')
            ->with('success', "Organization \"{$organization->name}\" created successfully.");
    }

    public function edit(Organization $organization)
    {
        $organization->loadCount(['users', 'vehicles']);

        return view('organizations.edit', [
            'organization' => $organization,
            'timezones'    => $this->timezones(),
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'timezone'      => ['required', Rule::in(array_keys($this->timezones()))],
            'brand_name'    => ['nullable', 'string', 'max:100'],
            'logo_url'      => ['nullable', 'url', 'max:500'],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'custom_domain' => ['nullable', 'string', 'max:253'],
        ]);

        // Ensure custom domain is not already claimed by another org.
        if ($request->filled('custom_domain')) {
            $taken = Organization::where('id', '!=', $organization->id)
                ->where('settings->custom_domain', $request->input('custom_domain'))
                ->exists();

            if ($taken) {
                return back()->withErrors(['custom_domain' => 'This domain is already assigned to another organization.'])->withInput();
            }
        }

        $settings = (array) ($organization->settings ?? []);
        foreach (['brand_name', 'logo_url', 'primary_color', 'custom_domain'] as $key) {
            $value = $request->input($key);
            if (filled($value)) {
                $settings[$key] = $value;
            } else {
                unset($settings[$key]);
            }
        }

        $organization->update([
            'name'     => $request->input('name'),
            'timezone' => $request->input('timezone'),
            'settings' => $settings,
        ]);

        AuditLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => 'organization_updated',
            'auditable_type'  => Organization::class,
            'auditable_id'    => $organization->id,
            'metadata'        => ['name' => $organization->name],
        ]);

        return redirect()->route('organizations.index')
            ->with('success', "Organization \"{$organization->name}\" updated successfully.");
    }

    public function destroy(Organization $organization)
    {
        $userCount    = $organization->users()->count();
        $vehicleCount = $organization->vehicles()->count();

        if ($userCount > 0 || $vehicleCount > 0) {
            return back()->withErrors([
                'organization' => "Cannot delete \"{$organization->name}\": it still has {$userCount} user(s) and {$vehicleCount} vehicle(s). Remove them first.",
            ]);
        }

        $name = $organization->name;
        $organization->delete();

        AuditLog::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id'         => Auth::id(),
            'event'           => 'organization_deleted',
            'auditable_type'  => Organization::class,
            'auditable_id'    => $organization->id,
            'metadata'        => ['name' => $name],
        ]);

        return redirect()->route('organizations.index')
            ->with('success', "Organization \"{$name}\" deleted.");
    }

    private function uniqueSlug(string $name): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 2;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    private function timezones(): array
    {
        return [
            'UTC'                 => 'UTC',
            'America/New_York'    => 'Eastern Time (US & Canada)',
            'America/Chicago'     => 'Central Time (US & Canada)',
            'America/Denver'      => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Anchorage'   => 'Alaska',
            'Pacific/Honolulu'    => 'Hawaii',
            'Europe/London'       => 'London',
            'Europe/Paris'        => 'Paris / Brussels',
            'Europe/Berlin'       => 'Berlin / Frankfurt',
            'Europe/Rome'         => 'Rome / Madrid / Vienna',
            'Europe/Moscow'       => 'Moscow',
            'Africa/Abidjan'      => 'West Africa — Abidjan / Dakar',
            'Africa/Lagos'        => 'West Africa — Lagos',
            'Africa/Nairobi'      => 'East Africa — Nairobi',
            'Africa/Johannesburg' => 'South Africa — Johannesburg',
            'Africa/Cairo'        => 'Cairo',
            'Asia/Dubai'          => 'Dubai / Abu Dhabi',
            'Asia/Karachi'        => 'Karachi',
            'Asia/Kolkata'        => 'India',
            'Asia/Dhaka'          => 'Dhaka',
            'Asia/Bangkok'        => 'Bangkok / Jakarta',
            'Asia/Singapore'      => 'Singapore / Kuala Lumpur',
            'Asia/Shanghai'       => 'China',
            'Asia/Tokyo'          => 'Tokyo',
            'Asia/Seoul'          => 'Seoul',
            'Australia/Sydney'    => 'Sydney',
            'Pacific/Auckland'    => 'Auckland',
        ];
    }
}
