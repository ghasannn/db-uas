<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperadminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');

        if (!$guard->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu dengan akun Superadmin.');
        }

        if (!$guard->user()->isSuperadmin()) {
            return redirect()->route('admin.login')->with('error', 'Fitur ini khusus untuk Superadmin. Silakan login sebagai Superadmin.');
        }

        return $next($request);
    }
}
