@extends('layouts.app')

@section('title', 'Organizations')
@section('page-title', 'Organizations')

@section('header-actions')
    <a href="{{ route('organizations.create') }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Add Organization
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Timezone</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Users</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Vehicles</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($organizations as $org)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $org->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $org->slug }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $org->timezone }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $org->users_count }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $org->vehicles_count }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $org->created_at->toDateString() }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('organizations.edit', $org) }}" class="btn-secondary py-1 text-xs">Edit</a>
                                @if ($org->users_count === 0 && $org->vehicles_count === 0)
                                    <form method="POST" action="{{ route('organizations.destroy', $org) }}"
                                        data-confirm="Delete organization &quot;{{ $org->name }}&quot;? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger py-1 text-xs">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">No organizations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($organizations->hasPages())
    <div class="mt-4">{{ $organizations->links() }}</div>
@endif
@endsection
