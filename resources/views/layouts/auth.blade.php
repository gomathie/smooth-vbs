<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign In') — {{ $branding['brand_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($branding['primary_color'])
    <style>
        :root { --brand: {{ $branding['primary_color'] }}; }
        .btn-primary { background-color: var(--brand) !important; }
        .btn-primary:hover { background-color: color-mix(in srgb, var(--brand) 85%, black) !important; }
        .bg-blue-600 { background-color: var(--brand) !important; }
        .form-input:focus, .form-select:focus {
            border-color: var(--brand) !important;
            --tw-ring-color: color-mix(in srgb, var(--brand) 20%, transparent) !important;
        }
    </style>
    @endif
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased">

<div class="flex min-h-screen">
    {{-- Left: branding panel --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col items-center justify-center bg-slate-900 px-12 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 mb-6 shadow-lg">
            @if ($branding['logo_url'])
                <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['brand_name'] }}" class="h-10 w-auto rounded object-contain">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-9 w-9 text-white">
                    <path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25ZM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 1 1 6 0h3a.75.75 0 0 0 .75-.75V15Z"/>
                    <path d="M8.25 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0ZM15.75 6.75a.75.75 0 0 0-.75.75v11.25c0 .087.015.17.042.248a3 3 0 0 1 5.958.464c.853-.175 1.522-.935 1.464-1.883a18.659 18.659 0 0 0-3.732-10.104 1.837 1.837 0 0 0-1.47-.725H15.75Z"/>
                    <path d="M19.5 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>
                </svg>
            @endif
        </div>
        <h1 class="text-3xl font-bold text-white mb-3">{{ $branding['brand_name'] }}</h1>
        <p class="text-slate-400 text-base max-w-xs leading-relaxed">
            Enterprise fleet booking &amp; monitoring platform for modern organizations.
        </p>

        <div class="mt-12 grid grid-cols-2 gap-6 text-left w-full max-w-sm">
            <div class="rounded-xl bg-slate-800 p-4">
                <p class="text-2xl font-bold text-white">Fleet</p>
                <p class="text-sm text-slate-400 mt-1">Manage your vehicles in one place</p>
            </div>
            <div class="rounded-xl bg-slate-800 p-4">
                <p class="text-2xl font-bold text-white">Book</p>
                <p class="text-sm text-slate-400 mt-1">Request and approve bookings fast</p>
            </div>
        </div>
    </div>

    {{-- Right: form --}}
    <div class="flex flex-1 flex-col items-center justify-center px-6 py-12">
        {{-- Mobile logo --}}
        <div class="mb-8 flex items-center gap-3 lg:hidden">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600">
                @if ($branding['logo_url'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['brand_name'] }}" class="h-6 w-auto rounded object-contain">
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-white">
                        <path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25ZM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 1 1 6 0h3a.75.75 0 0 0 .75-.75V15Z"/>
                        <path d="M8.25 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0ZM15.75 6.75a.75.75 0 0 0-.75.75v11.25c0 .087.015.17.042.248a3 3 0 0 1 5.958.464c.853-.175 1.522-.935 1.464-1.883a18.659 18.659 0 0 0-3.732-10.104 1.837 1.837 0 0 0-1.47-.725H15.75Z"/>
                        <path d="M19.5 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z"/>
                    </svg>
                @endif
            </div>
            <span class="text-xl font-bold text-slate-900">{{ $branding['brand_name'] }}</span>
        </div>

        <div class="w-full max-w-sm">
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
