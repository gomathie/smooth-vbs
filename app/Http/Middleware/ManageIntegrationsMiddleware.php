<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageIntegrationsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canManageIntegrations()) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
