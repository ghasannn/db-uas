<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizerApproved
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isOrganizer()) {
            $org = $user->organization;

            if (!$org || $org->status !== 'approved') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Akun organisasi Anda belum disetujui oleh Superadmin.'], 403);
                }
                return redirect()->route('organizer.dashboard')->with('warning', 'Akun organisasi Anda masih dalam status PENDING approval. Anda belum bisa mempublikasikan event.');
            }
        }

        return $next($request);
    }
}
