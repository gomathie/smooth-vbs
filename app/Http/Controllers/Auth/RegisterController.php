<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show(): \Illuminate\Contracts\View\View
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
        ]);

        $organizationName = trim($request->input('organization_name'));
        $slug = Str::slug($organizationName) ?: 'organization-' . time();

        $organization = Organization::firstOrCreate(
            ['slug' => $slug],
            ['name' => $organizationName, 'timezone' => config('app.timezone', 'UTC')]
        );

        $role = Organization::count() === 1 ? User::ROLE_ORGANIZATION_ADMIN : User::ROLE_EMPLOYEE;

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'organization_id' => $organization->id,
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
