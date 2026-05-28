@extends('layouts.app')

@section('title', 'Edit Organization')
@section('page-title', 'Edit Organization')

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
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">{{ $organization->name }}</h2>
            <p class="font-mono text-xs text-slate-400">{{ $organization->slug }}</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('organizations.update', $organization) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name"
                        value="{{ old('name', $organization->name) }}"
                        required class="form-input">
                </div>

                <div>
                    <label for="timezone" class="form-label">Timezone <span class="text-red-500">*</span></label>
                    <select id="timezone" name="timezone" required class="form-select">
                        @foreach ($timezones as $tz => $label)
                            <option value="{{ $tz }}" @selected(old('timezone', $organization->timezone) === $tz)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">
                    <div>
                        <span class="font-medium text-slate-700">Users:</span> {{ $organization->users_count }}
                    </div>
                    <div>
                        <span class="font-medium text-slate-700">Vehicles:</span> {{ $organization->vehicles_count }}
                    </div>
                    <div>
                        <span class="font-medium text-slate-700">Created:</span> {{ $organization->created_at->toDateString() }}
                    </div>
                    <div>
                        <span class="font-medium text-slate-700">Slug:</span>
                        <span class="font-mono">{{ $organization->slug }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('organizations.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- White Label settings --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">White Label</h2>
            <p class="text-xs text-slate-400">Customize how this organization's users see the app.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('organizations.update', $organization) }}" class="space-y-5">
                @csrf
                @method('PUT')
                {{-- Pass the main fields through hidden inputs so the same update() handles both --}}
                <input type="hidden" name="name" value="{{ $organization->name }}">
                <input type="hidden" name="timezone" value="{{ $organization->timezone }}">

                <div>
                    <label for="brand_name" class="form-label">Brand Name</label>
                    <input id="brand_name" type="text" name="brand_name"
                        value="{{ old('brand_name', $organization->settings['brand_name'] ?? '') }}"
                        class="form-input" placeholder="Acme Fleet Manager">
                    <p class="mt-1.5 text-xs text-slate-400">Replaces "{{ config('app.name') }}" in the sidebar and page titles.</p>
                </div>

                <div>
                    <label for="logo_url" class="form-label">Logo URL</label>
                    <input id="logo_url" type="url" name="logo_url"
                        value="{{ old('logo_url', $organization->settings['logo_url'] ?? '') }}"
                        class="form-input" placeholder="https://cdn.acme.com/logo.png">
                    <p class="mt-1.5 text-xs text-slate-400">PNG or SVG, ideally square. Shown at 32×32px in the sidebar.</p>
                </div>

                <div>
                    <label for="primary_color" class="form-label">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color_picker" id="primary_color_picker"
                            value="{{ old('primary_color', $organization->settings['primary_color'] ?? '#2563eb') }}"
                            class="h-10 w-12 cursor-pointer rounded-lg border border-slate-300 p-0.5"
                            oninput="document.getElementById('primary_color').value = this.value">
                        <input id="primary_color" type="text" name="primary_color"
                            value="{{ old('primary_color', $organization->settings['primary_color'] ?? '') }}"
                            class="form-input font-mono" placeholder="#2563eb" maxlength="7"
                            oninput="document.getElementById('primary_color_picker').value = this.value">
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Hex color for buttons, active nav, and accents.</p>
                </div>

                <div>
                    <label for="custom_domain" class="form-label">Custom Domain</label>
                    <input id="custom_domain" type="text" name="custom_domain"
                        value="{{ old('custom_domain', $organization->settings['custom_domain'] ?? '') }}"
                        class="form-input font-mono" placeholder="fleet.acme.com">
                    <p class="mt-1.5 text-xs text-slate-400">When users visit this domain, they see this organization's branding on the login page.</p>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="submit" class="btn-primary">Save White Label</button>
                </div>
            </form>
        </div>
    </div>

    @if ($organization->users_count === 0 && $organization->vehicles_count === 0)
        <div class="card border-red-200">
            <div class="card-body flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-900">Delete organization</p>
                    <p class="mt-0.5 text-xs text-slate-400">Permanently removes this organization. Only available when it has no users or vehicles.</p>
                </div>
                <form method="POST" action="{{ route('organizations.destroy', $organization) }}"
                    data-confirm="Delete &quot;{{ $organization->name }}&quot;? This cannot be undone.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
