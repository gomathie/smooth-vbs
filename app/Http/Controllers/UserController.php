<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization_id;

        $users = User::where('organization_id', $organizationId)
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', ['roles' => User::roles()]);
    }

    public function store(Request $request)
    {
        $organizationId = Auth::user()->organization_id;

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(User::roles())],
            'can_manage_integrations' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'organization_id' => $organizationId,
            'name'            => $request->input('name'),
            'email'           => $request->input('email'),
            'password'        => Hash::make($request->input('password')),
            'role'            => $request->input('role'),
            'is_active'       => true,
            'can_manage_integrations' => Auth::user()->role === User::ROLE_SUPER_ADMIN && $request->boolean('can_manage_integrations'),
        ]);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id'         => Auth::id(),
            'event'           => 'user_created',
            'auditable_type'  => User::class,
            'auditable_id'    => $user->id,
            'metadata'        => ['role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $user = User::where('organization_id', $organizationId)->findOrFail($id);

        return view('users.edit', ['user' => $user, 'roles' => User::roles()]);
    }

    public function update(Request $request, string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $user = User::where('organization_id', $organizationId)->findOrFail($id);

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', Rule::in(User::roles())],
            'can_manage_integrations' => ['sometimes', 'boolean'],
        ]);

        // Prevent demoting the last active admin in the organization.
        $newRoleIsAdmin = in_array($request->input('role'), [User::ROLE_ORGANIZATION_ADMIN, User::ROLE_SUPER_ADMIN], true);
        if ($user->isAdmin() && ! $newRoleIsAdmin) {
            $adminCount = User::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->whereIn('role', [User::ROLE_ORGANIZATION_ADMIN, User::ROLE_SUPER_ADMIN])
                ->count();

            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'Cannot change role: this is the last active administrator in your organization.'])->withInput();
            }
        }

        $changes = [];
        if ($user->name !== $request->input('name')) {
            $changes['name'] = [$user->name, $request->input('name')];
        }
        if ($user->role !== $request->input('role')) {
            $changes['role'] = [$user->role, $request->input('role')];
        }

        $attributes = $request->only(['name', 'email', 'role']);
        if (Auth::user()->role === User::ROLE_SUPER_ADMIN) {
            $attributes['can_manage_integrations'] = $request->boolean('can_manage_integrations');
            if ($user->can_manage_integrations !== $attributes['can_manage_integrations']) {
                $changes['can_manage_integrations'] = [$user->can_manage_integrations, $attributes['can_manage_integrations']];
            }
        }

        $user->update($attributes);

        if ($changes) {
            AuditLog::create([
                'organization_id' => $organizationId,
                'user_id'         => Auth::id(),
                'event'           => 'user_updated',
                'auditable_type'  => User::class,
                'auditable_id'    => $user->id,
                'metadata'        => $changes,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(string $id)
    {
        $organizationId = Auth::user()->organization_id;
        $user = User::where('organization_id', $organizationId)->findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        // Prevent deactivating the last active admin.
        if ($user->isAdmin()) {
            $adminCount = User::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->whereIn('role', [User::ROLE_ORGANIZATION_ADMIN, User::ROLE_SUPER_ADMIN])
                ->count();

            if ($adminCount <= 1) {
                return back()->withErrors(['user' => 'Cannot deactivate the last active administrator.']);
            }
        }

        $newState = ! $user->is_active;
        $user->update(['is_active' => $newState]);

        AuditLog::create([
            'organization_id' => $organizationId,
            'user_id'         => Auth::id(),
            'event'           => $newState ? 'user_activated' : 'user_deactivated',
            'auditable_type'  => User::class,
            'auditable_id'    => $user->id,
            'metadata'        => ['is_active' => $newState],
        ]);

        $label = $newState ? 'activated' : 'deactivated';

        return redirect()->route('users.index')->with('success', "User {$label} successfully.");
    }
}
