@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div>
    <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
    <p class="mt-1 text-sm text-slate-500">Sign in to your Smooth VBS account.</p>
</div>

@if ($errors->any())
    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
        <ul class="space-y-1 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="email" class="form-label">Email address</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            class="form-input @error('email') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror"
            placeholder="you@company.com"
        >
    </div>

    <div>
        <label for="password" class="form-label">Password</label>
        <input
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            class="form-input"
            placeholder="••••••••"
        >
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            Remember me
        </label>
    </div>

    <button type="submit" class="btn-primary w-full justify-center py-3">
        Sign in
    </button>
</form>

<p class="mt-6 text-center text-sm text-slate-500">
    Don't have an account?
    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">Create one</a>
</p>
@endsection
