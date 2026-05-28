@extends('layouts.app')

@section('title', 'Edit Platform Integration')
@section('page-title', 'Edit Platform Integration')

@section('header-actions')
    <a href="{{ route('gps.index') }}" class="btn-secondary">
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
            <h2 class="text-sm font-semibold text-slate-900">{{ $integration->label }}</h2>
            <p class="text-xs text-slate-400">Last synced: {{ $integration->last_sync_at?->diffForHumans() ?? 'Never' }}</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('gps.update', $integration) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="label" class="form-label">Label <span class="text-red-500">*</span></label>
                        <input id="label" type="text" name="label" value="{{ old('label', $integration->label) }}"
                            required class="form-input">
                    </div>

                    <div>
                        <label for="provider" class="form-label">Provider <span class="text-red-500">*</span></label>
                        <select id="provider" name="provider" required class="form-select" onchange="toggleBaseUrl(this.value)">
                            @foreach ($providers as $value => $label)
                                <option value="{{ $value }}" @selected(old('provider', $integration->provider) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="pilot-node-field" class="{{ old('provider', $integration->provider) !== 'pilot_telematics' ? 'hidden' : '' }}">
                    <label for="pilot_node" class="form-label">Node</label>
                    <select id="pilot_node" name="pilot_node" class="form-select">
                        @for ($n = 1; $n <= 15; $n++)
                            <option value="{{ $n }}" @selected(old('pilot_node', $integration->config['node'] ?? 1) == $n)>Node {{ $n }}</option>
                        @endfor
                    </select>
                    <p class="mt-1.5 text-xs text-slate-400">The node number used in the Pilot Telematics API call.</p>
                </div>

                <div id="base-url-field">
                    <label for="base_url" class="form-label">Server URL</label>
                    <input id="base_url" type="url" name="base_url"
                        value="{{ old('base_url', $integration->base_url) }}"
                        class="form-input" placeholder="https://yourserver.com">
                </div>

                <div>
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="Active" @selected(old('status', $integration->status) === 'Active')>Active</option>
                        <option value="Inactive" @selected(old('status', $integration->status) === 'Inactive')>Inactive</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="username" class="form-label">Username / API key <span class="text-red-500">*</span></label>
                        <input id="username" type="text" name="username"
                            value="{{ old('username', $integration->username) }}"
                            required class="form-input" autocomplete="off">
                    </div>

                    <div>
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" name="password"
                            class="form-input" placeholder="Leave blank to keep current" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('gps.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="card border-red-200">
        <div class="card-body flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-900">Remove integration</p>
                <p class="mt-0.5 text-xs text-slate-400">This will stop location syncing. Existing vehicle positions are kept.</p>
            </div>
            <form method="POST" action="{{ route('gps.destroy', $integration) }}" data-confirm="Remove this GPS integration?">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Remove</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBaseUrl(provider) {
    document.getElementById('base-url-field').style.display = provider === 'demo' ? 'none' : '';
    document.getElementById('pilot-node-field').classList.toggle('hidden', provider !== 'pilot_telematics');
}
toggleBaseUrl(document.getElementById('provider').value);
</script>
@endsection
