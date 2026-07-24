<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth provider (or mock for local testing).
     */
    public function redirectToGoogle(Request $request)
    {
        // Store intended URL if coming from checkout or specific page
        if ($request->has('redirect_to')) {
            session(['intended_url' => $request->get('redirect_to')]);
        }

        // Mock fallback for local testing when credentials are not configured or ?mock=1
        if ($request->has('mock') || empty(config('services.google.client_id'))) {
            return $this->handleMockCallback($request);
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error('Google OAuth Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Gagal terhubung ke Google SSO: ' . $e->getMessage());
        }
    }

    /**
     * Handle callback from Google OAuth.
     */
    public function handleGoogleCallback(Request $request)
    {
        // Handle mock callback if triggered
        if ($request->has('mock') || empty(config('services.google.client_id'))) {
            return $this->handleMockCallback($request);
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->route('login')->with('error', 'Gagal mendapatkan data akun dari Google.');
            }

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link existing account with Google provider without altering role
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                ]);
            } else {
                // Create new buyer account
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'Google User',
                    'email' => $googleUser->getEmail(),
                    'password' => null,
                    'role' => 'buyer',
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            Auth::guard('web')->login($user, true);

            if ($user->isSuperadmin() || $user->isOrganizer()) {
                Auth::guard('web')->logout();
                return redirect()->route('admin.login')->with('error', 'Akun ini adalah akun Admin / Penyelenggara. Silakan login melalui Portal Management Admin.');
            }

            $intendedUrl = session()->pull('intended_url', route('home'));
            return redirect()->to($intendedUrl)->with('success', 'Selamat datang, ' . $user->name . '!');
        } catch (\Exception $e) {
            Log::error('Google Callback Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login via Google: ' . $e->getMessage());
        }
    }

    /**
     * Mock SSO handler for local dev testing.
     */
    protected function handleMockCallback(Request $request)
    {
        $mockEmail = $request->get('email', 'google_buyer@amikom.ac.id');
        $mockName = $request->get('name', 'Pembeli SSO Google');

        $user = User::where('email', $mockEmail)->first();

        if ($user) {
            $user->update([
                'provider' => 'google',
                'provider_id' => 'mock_google_id_12345',
            ]);
        } else {
            $user = User::create([
                'name' => $mockName,
                'email' => $mockEmail,
                'password' => null,
                'role' => 'buyer',
                'provider' => 'google',
                'provider_id' => 'mock_google_id_12345',
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($mockName),
            ]);
        }

        Auth::guard('web')->login($user, true);

        if ($user->isSuperadmin() || $user->isOrganizer()) {
            Auth::guard('web')->logout();
            return redirect()->route('admin.login')->with('error', 'Akun ini adalah akun Admin / Penyelenggara. Silakan login melalui Portal Management Admin.');
        }

        $intendedUrl = session()->pull('intended_url', route('home'));
        return redirect()->to($intendedUrl)->with('success', '[MOCK SSO] Selamat datang, ' . $user->name . '!');
    }
}
