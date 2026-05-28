@extends('layouts.app')

@section('title', 'Add Platform Integration')
@section('page-title', 'Add Platform Integration')

@section('header-actions')
    <a href="{{ route('gps.index') }}" class="btn-secondary">
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
            <h2 class="text-sm font-semibold text-slate-900">GPS Provider Credentials</h2>
            <p class="text-xs text-slate-400">Credentials are stored encrypted.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('gps.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="label" class="form-label">Label <span class="text-red-500">*</span></label>
                        <input id="label" type="text" name="label" value="{{ old('label') }}"
                            required class="form-input" placeholder="Main fleet tracker">
                    </div>

                    <div>
                        <label for="provider" class="form-label">Provider <span class="text-red-500">*</span></label>
                        <select id="provider" name="provider" required class="form-select" onchange="toggleBaseUrl(this.value)">
                            @foreach ($providers as $value => $label)
                                <option value="{{ $value }}" @selected(old('provider') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="pilot-node-field" class="hidden">
                    <label for="pilot_node" class="form-label">Node</label>
                    <select id="pilot_node" name="pilot_node" class="form-select">
                        @for ($n = 1; $n <= 15; $n++)
                            <option value="{{ $n }}" @selected(old('pilot_node', 1) == $n)>Node {{ $n }}</option>
                        @endfor
                    </select>
                    <p class="mt-1.5 text-xs text-slate-400">The node number used in the Pilot Telematics API call.</p>
                </div>

                <div id="base-url-field">
                    <label for="base_url" class="form-label">Server URL</label>
                    <input id="base_url" type="url" name="base_url" value="{{ old('base_url') }}"
                        class="form-input" placeholder="https://your-traccar-server.com">
                    <p class="mt-1.5 text-xs text-slate-400">
                        Required for Pilot Telematics and Traccar. Not needed for Demo mode.
                        <span id="pilot-hint" class="hidden">
                            Enter the base server address — the driver appends <code>/api/api.php</code> automatically.
                        </span>
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="username" class="form-label">Username / API key <span class="text-red-500">*</span></label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}"
                            required class="form-input" placeholder="admin@example.com" autocomplete="off">
                    </div>

                    <div>
                        <label for="password" class="form-label">Password / Secret <span class="text-red-500">*</span></label>
                        <input id="password" type="password" name="password"
                            required class="form-input" placeholder="••••••••" autocomplete="new-password">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <a href="{{ route('gps.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Integration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBaseUrl(provider) {
    document.getElementById('base-url-field').style.display = provider === 'demo' ? 'none' : '';
    document.getElementById('pilot-hint').classList.toggle('hidden', provider !== 'pilot_telematics');
    document.getElementById('pilot-node-field').classList.toggle('hidden', provider !== 'pilot_telematics');
}
toggleBaseUrl(document.getElementById('provider').value);
</script>
@endsection
