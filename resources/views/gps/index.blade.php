@extends('layouts.app')

@section('title', 'Telematics Integrations')
@section('page-title', 'Telematics Platforms')

@section('header-actions')
    <a href="{{ route('gps.map') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>
        </svg>
        Live Map
    </a>
    <a href="{{ route('gps.create') }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
        </svg>
        Add Integration
    </a>
@endsection

@section('content')

{{-- Info banner --}}
<div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
    <strong>How it works:</strong>
    The system pulls vehicle locations from your GPS provider every 3 minutes via a cron scheduler.
    Vehicles must have a <strong>GPS Vehicle ID</strong> set on their profile to be matched.
    Add the following line to your server crontab:
    <code class="mt-1 block rounded bg-blue-100 px-2 py-1 font-mono text-xs">* * * * * php {{ base_path('artisan') }} schedule:run >> /dev/null 2>&1</code>
</div>

<div class="card">
    @if ($integrations->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-14 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10 text-slate-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <p class="text-sm text-slate-400">No platform integrations configured.</p>
            <a href="{{ route('gps.create') }}" class="mt-2 text-sm font-medium text-blue-600 hover:text-blue-700">Add your first integration →</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/60">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Label</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Last Sync</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($integrations as $integration)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $integration->label }}</td>
                            <td class="px-6 py-4 text-slate-600 capitalize">
                                {{ \App\Models\GpsIntegration::PROVIDERS[$integration->provider] ?? $integration->provider }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $integration->username }}</td>
                            <td class="px-6 py-4">
                                @if ($integration->status === 'Active')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Active
                                    </span>
                                @elseif ($integration->status === 'Error')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Error
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ $integration->last_sync_at ? $integration->last_sync_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('gps.edit', $integration) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('gps.destroy', $integration) }}" data-confirm="Remove this platform integration?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger py-1.5 px-3 text-xs">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
