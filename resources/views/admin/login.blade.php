<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Login - AmikomEventHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    <!-- Ambient Background Lighting -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 shadow-2xl relative z-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl text-white font-black text-2xl mb-4 shadow-lg shadow-indigo-500/20">
                AH
            </div>
            <h1 class="text-2xl font-black text-white">Portal Management</h1>
            <p class="text-slate-400 text-sm mt-1">Khusus Superadmin & Organizer / Kepanitiaan</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 font-semibold text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-2xl mb-6 font-semibold text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-2xl mb-6 font-semibold text-sm text-center">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wider">Email Internal / Organizer</label>
                <input type="email" name="email" class="w-full px-5 py-3.5 bg-slate-800/80 border border-slate-700 rounded-2xl text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm placeholder-slate-500" placeholder="admin@amikom.ac.id / hima@amikom.ac.id" required>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-300 mb-2 uppercase tracking-wider">Password</label>
                <input type="password" name="password" class="w-full px-5 py-3.5 bg-slate-800/80 border border-slate-700 rounded-2xl text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition font-medium text-sm placeholder-slate-500" placeholder="••••••••" required>
            </div>
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl font-bold text-base shadow-lg shadow-indigo-600/30 hover:opacity-95 transition">
                Masuk ke Portal Management
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-500">Bukan pengelola event? <a href="{{ route('login') }}" class="text-indigo-400 hover:underline font-semibold">Login sebagai Pembeli &rarr;</a></p>
        </div>
    </div>
</body>
</html>
