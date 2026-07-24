<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');

        if (!$guard->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu dengan akun Admin atau Organizer.');
        }

        $user = $guard->user();

        if (!$user->isOrganizer() && !$user->isSuperadmin()) {
            $guard->logout();
            return redirect()->route('admin.login')->with('error', 'Silakan login dengan akun Admin atau Organizer untuk mengakses halaman dashboard.');
        }

        return $next($request);
    }
}
