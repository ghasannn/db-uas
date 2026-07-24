<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizerRegistrationController extends Controller
{
    public function showRegisterForm()
    {
        return view('organizer.register');
    }

    public function register(Request $request)
    {
        $userId = Auth::id();

        $rules = [
            'organization_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . ($userId ?? 'NULL'),
            'password' => $userId ? 'nullable|string|min:6|confirmed' : 'required|string|min:6|confirmed',
        ];

        $request->validate($rules, [
            'organization_name.required' => 'Nama Organisasi / Kepanitiaan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar di sistem. Gunakan email lain.',
            'password.required' => 'Password wajib diisi untuk login ke Dashboard Admin.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        DB::transaction(function () use ($request, $userId) {
            if (!$userId) {
                $user = User::create([
                    'name' => $request->organization_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role' => 'organizer_owner',
                ]);
            } else {
                $user = User::find($userId);
                $updateData = [
                    'name' => $request->organization_name,
                    'email' => $request->email,
                    'role' => 'organizer_owner',
                ];
                if ($request->filled('password')) {
                    $updateData['password'] = bcrypt($request->password);
                }
                $user->update($updateData);
            }

            $slug = Str::slug($request->organization_name);
            $count = Organization::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $org = Organization::create([
                'name' => $request->organization_name,
                'slug' => $slug,
                'owner_user_id' => $user->id,
                'description' => 'Penyelenggara event ' . $request->organization_name,
                'status' => 'pending',
            ]);

            $user->update(['organization_id' => $org->id]);
        });

        // Logout active session to avoid account collision and force user to log in with new credentials
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Pendaftaran Pengelola Event berhasil dikirim! Silakan melakukan login terlebih dahulu menggunakan Email dan Password yang telah Anda daftarkan. Status organisasi Anda saat ini PENDING dan menunggu persetujuan (ACC) dari Superadmin.');
    }
}
