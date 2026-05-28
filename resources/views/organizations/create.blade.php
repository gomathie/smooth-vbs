@extends('layouts.app')

@section('title', 'Add Organization')
@section('page-title', 'Add Organization')

@section('header-actions')
    <a href="{{ route('organizations.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <form method="POST" action="{{ route('organizations.store') }}" class="space-y-6">
        @csrf

        {{-- Organization details --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Organization Details</h2>
            </div>
            <div class="card-body space-y-5">
                <div>
                    <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                        required class="form-input" placeholder="Acme Transport Ltd.">
                </div>

                <div>
                    <label for="timezone" class="form-label">Timezone <span class="text-red-500">*</span></label>
                    <select id="timezone" name="timezone" required class="form-select">
                        @foreach ($timezones as $tz => $label)
                            <option value="{{ $tz }}" @selected(old('timezone', 'UTC') === $tz)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Initial admin (optional) --}}
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Initial Administrator <span class="ml-1 text-xs font-normal text-slate-400">(optional)</span></h2>
                <p class="text-xs text-slate-400">Create an Organization Admin account for this organization. You can also add users later.</p>
            </div>
            <div class="card-body space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="admin_name" class="form-label">Full Name</label>
                        <input id="admin_name" type="text" name="admin_name" value="{{ old('admin_name') }}"
                            class="form-input" placeholder="Jane Smith" autocomplete="off">
                    </div>

                    <div>
                        <label for="admin_email" class="form-label">Email Address</label>
                        <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}"
                            class="form-input" placeholder="jane@acme.com" autocomplete="off">
                    </div>
                </div>

                <div>
                    <label for="admin_password" class="form-label">Password</label>
                    <input id="admin_password" type="password" name="admin_password"
                        class="form-input" placeholder="Min. 8 characters" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('organizations.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Organization</button>
        </div>
    </form>
</div>
@endsection
