<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Show login page (Buyer vs Admin/Organizer)
    public function showLogin(Request $request) {
        if ($request->is('admin*') || $request->routeIs('admin.*')) {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }
            return view('admin.login');
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    // 2. Submit Log In
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $isAdminRoute = $request->is('admin*') || $request->routeIs('admin.*');

        // Handling login attempt on Admin Portal (/admin/login)
        if ($isAdminRoute) {
            if (Auth::guard('admin')->attempt($credentials)) {
                $user = Auth::guard('admin')->user();

                if ($user->isSuperadmin() || $user->isOrganizer()) {
                    $request->session()->regenerate();
                    return redirect()->route('admin.dashboard');
                } else {
                    Auth::guard('admin')->logout();
                    return back()->withErrors([
                        'email' => 'Akun ini adalah akun Pembeli. Silakan login melalui portal Pembeli.',
                    ]);
                }
            }

            return back()->withErrors([
                'email' => 'Email atau Password yang Anda berikan tidak terdaftar di database kami.',
            ]);
        }

        // Handling login attempt on Buyer Portal (/login)
        if (Auth::guard('web')->attempt($credentials)) {
            $user = Auth::guard('web')->user();

            if ($user->isSuperadmin() || $user->isOrganizer()) {
                Auth::guard('web')->logout();
                return back()->withErrors([
                    'email' => 'Akun ini adalah akun Admin / Penyelenggara. Silakan login melalui Portal Management Admin.',
                ]);
            }

            $request->session()->regenerate();
            $intendedUrl = session()->pull('intended_url', route('home'));
            return redirect()->to($intendedUrl);
        }

        return back()->withErrors([
            'email' => 'Email atau Password yang Anda berikan tidak terdaftar di database kami.',
        ]);
    }

    // 3. Log Out
    public function logout(Request $request) {
        $isAdminRoute = $request->is('admin*') || $request->routeIs('admin.*');

        if ($isAdminRoute) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login');
        }

        Auth::guard('web')->logout();
        return redirect('/');
    }
}
