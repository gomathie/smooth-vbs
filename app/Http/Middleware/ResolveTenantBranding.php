<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantBranding
{
    public function handle(Request $request, Closure $next): Response
    {
        $branding = $this->defaults();

        // 1. Try to match a custom domain first (works even before login).
        $host = $request->getHost();
        $org  = Organization::where('settings->custom_domain', $host)->first();

        // 2. Fall back to the authenticated user's own organization.
        if (! $org && $request->user()) {
            $org = $request->user()->organization;
        }

        if ($org) {
            $settings = (array) ($org->settings ?? []);
            $branding = [
                'brand_name'    => $settings['brand_name'] ?? config('app.name', 'Smooth VBS'),
                'logo_url'      => $settings['logo_url'] ?? null,
                'primary_color' => $this->validHex($settings['primary_color'] ?? null),
                'custom_domain' => $settings['custom_domain'] ?? null,
            ];
        }

        View::share('branding', $branding);

        return $next($request);
    }

    private function defaults(): array
    {
        return [
            'brand_name'    => config('app.name', 'Smooth VBS'),
            'logo_url'      => null,
            'primary_color' => null,
            'custom_domain' => null,
        ];
    }

    private function validHex(?string $color): ?string
    {
        if (! $color) {
            return null;
        }

        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : null;
    }
}
