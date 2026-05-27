@extends('layouts.auth')

@section('title', 'Create Account')

@section('content')
<div>
    <h2 class="text-2xl font-bold text-slate-900">Create your account</h2>
    <p class="mt-1 text-sm text-slate-500">Set up your organization and get started.</p>
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

<form method="POST" action="{{ route('register.perform') }}" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="organization_name" class="form-label">Organization name</label>
        <input
            id="organization_name"
            type="text"
            name="organization_name"
            value="{{ old('organization_name') }}"
            required
            class="form-input"
            placeholder="Acme Corp"
        >
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="form-label">Full name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
                class="form-input"
                placeholder="John Doe"
            >
        </div>

        <div>
            <label for="email" class="form-label">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="form-input"
                placeholder="you@company.com"
            >
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="form-input"
                placeholder="Min. 8 characters"
            >
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="form-input"
                placeholder="••••••••"
            >
        </div>
    </div>

    <button type="submit" class="btn-primary w-full justify-center py-3">
        Create account
    </button>
</form>

<p class="mt-6 text-center text-sm text-slate-500">
    Already have an account?
    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Sign in</a>
</p>
@endsection
