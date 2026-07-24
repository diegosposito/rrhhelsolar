<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the admin-only PDF export routes.
 *
 * Mirrors the admin panel gate (User::canAccessPanel): only active users
 * holding the "admin" role may pass. Guests are redirected to the panel
 * login; authenticated non-admins receive a 403.
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect('/admin/login');
        }

        if ($user->activo !== true || $user->role?->nombre !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
