<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the standalone kiosk route.
 *
 * Enforced on every request so an administrator toggling `activo = false`
 * kills the kiosk session on its next interaction (remote invalidation).
 * Only active users holding the "admin" or "fichaje" role may pass.
 */
class EnsureKioskAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guests are sent to the standalone login screen.
        if ($user === null) {
            return redirect()->route('login');
        }

        // Remote invalidation: a deactivated account is logged out immediately.
        if ($user->activo !== true) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        // Only the two kiosk-capable roles may reach the punch screen.
        if (! in_array($user->role?->nombre, ['admin', 'fichaje'], true)) {
            abort(403);
        }

        return $next($request);
    }
}
