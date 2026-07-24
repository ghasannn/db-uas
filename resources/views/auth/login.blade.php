<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pembeli - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white text-slate-900 rounded-[2rem] p-8 shadow-2xl">
        <div class="text-center mb-8">
            <a href="/" class="inline-block w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4 hover:scale-105 transition-transform">AH</a>
            <h1 class="text-2xl font-black">Masuk Akun Pembeli</h1>
            <p class="text-slate-500 text-sm">Login untuk membeli & mengelola tiket event Anda</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-600 p-4 rounded-xl mb-6 font-bold text-sm text-center">
            {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-100 text-emerald-700 p-4 rounded-xl mb-6 font-bold text-sm text-center">
            {{ session('success') }}
            </div>
        @endif

        <!-- Google SSO Login Button (Fitur 4.1) -->
        <div class="mb-6">
            <a href="{{ route('auth.google') }}" class="w-full py-4 px-6 border-2 border-slate-200 rounded-2xl font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-3 shadow-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Continue with Google</span>
            </a>

            <!-- Mock Dev SSO option if local -->
            <div class="text-center mt-2">
                <a href="{{ route('auth.google', ['mock' => 1]) }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                    (Mode Dev: Klik untuk Mock Google Login Instant)
                </a>
            </div>
        </div>

        <div class="relative flex items-center justify-center mb-6">
            <hr class="w-full border-slate-200">
            <span class="absolute bg-white px-4 text-xs text-slate-400 font-semibold uppercase">Atau Login Email</span>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Email</label>
                <input type="email" name="email" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium" placeholder="nama@email.com" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2 uppercase tracking-wide">Password</label>
                <input type="password" name="password" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-lg shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition">Masuk Pembeli</button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-400">Pengelola event? <a href="{{ route('admin.login') }}" class="text-indigo-600 font-bold hover:underline">Login Management Portal &rarr;</a></p>
        </div>
    </div>
</body>
</html>
