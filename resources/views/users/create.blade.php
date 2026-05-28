@extends('layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('header-actions')
    <a href="{{ route('users.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">User Details</h2>
            <p class="text-xs text-slate-400">The user will be added to your organization.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Full name <span class="text-red-500">*</span></label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="form-input"
                            placeholder="Jane Smith"
                        >
                    </div>

                    <div>
                        <label for="email" class="form-label">Email address <span class="text-red-500">*</span></label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="form-input"
                            placeholder="jane@company.com"
                        >
                    </div>
                </div>

                <div>
                    <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="form-select">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>
                                {{ ucwords(str_replace('_', ' ', $role)) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1.5 text-xs text-slate-400">
                        employee → supervisor → fleet_manager → organization_admin
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="form-input"
                            placeholder="Min. 8 characters"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm password <span class="text-red-500">*</span></label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="form-input"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                @if (auth()->user()->role === 'super_admin')
                    <div class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <input
                            id="can_manage_integrations"
                            type="checkbox"
                            name="can_manage_integrations"
                            value="1"
                            @checked(old('can_manage_integrations'))
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >
                        <div>
                            <label for="can_manage_integrations" class="form-label">Allow platform integration management</label>
                            <p class="text-xs text-slate-500">Grant this user the right to add, edit, and remove telematics integrations.</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
