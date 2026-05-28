@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('header-actions')
    <a href="{{ route('users.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ $user->name }}</h2>
                <p class="mt-0.5 text-xs text-slate-400">{{ $user->email }}</p>
            </div>
            @if ($user->is_active)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                    Active
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                    Inactive
                </span>
            @endif
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="form-label">Full name <span class="text-red-500">*</span></label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label for="email" class="form-label">Email address <span class="text-red-500">*</span></label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="form-input"
                        >
                    </div>
                </div>

                <div>
                    <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="form-select"
                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                {{ ucwords(str_replace('_', ' ', $role)) }}
                            </option>
                        @endforeach
                    </select>
                    @if ($user->id === auth()->id())
                        {{-- Send current role as hidden input since the select is disabled --}}
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <p class="mt-1.5 text-xs text-slate-400">You cannot change your own role.</p>
                    @endif
                </div>

                @if (auth()->user()->role === 'super_admin')
                    <div class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <input
                            id="can_manage_integrations"
                            type="checkbox"
                            name="can_manage_integrations"
                            value="1"
                            @checked(old('can_manage_integrations', $user->can_manage_integrations))
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
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Deactivate / Reactivate --}}
    @if ($user->id !== auth()->id())
        <div class="card {{ $user->is_active ? 'border-red-200' : 'border-slate-200' }}">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-900">
                        {{ $user->is_active ? 'Deactivate account' : 'Reactivate account' }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-400">
                        {{ $user->is_active
                            ? 'The user will no longer be able to log in.'
                            : 'The user will be able to log in again.' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('users.destroy', $user) }}"
                      data-confirm="{{ $user->is_active ? 'Deactivate this user?' : 'Reactivate this user?' }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="{{ $user->is_active ? 'btn-danger' : 'btn-secondary' }}">
                        {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
