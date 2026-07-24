<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AmikomEventHub - Platform Ticketing Multi-Tenant & SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f8f5fc',
                            100: '#CAA8F5', // Mauve
                            200: '#e5d7fa',
                            300: '#9984D4', // Soft Periwinkle
                            400: '#795db8',
                            500: '#592E83', // Indigo Velvet
                            600: '#592E83', // Primary Brand Dark
                            700: '#432263',
                            800: '#230C33', // Midnight Violet
                            900: '#230C33',
                            950: '#160721',
                        },
                        mauve: '#CAA8F5',
                        periwinkle: '#9984D4', // Primary Soft Periwinkle Accent
                        velvet: '#592E83',
                        midnight: '#230C33',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f7f6fc;
        }

        .glass {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            border-color: #CAA8F5;
        }
    </style>
</head>

<body class="bg-[#f7f6fc] text-[#230C33]">

    <!-- Navigation -->
    <nav class="glass sticky top-4 z-40 mx-4 mt-4 px-6 py-4 rounded-2xl border border-[#CAA8F5]/40 shadow-lg shadow-[#592E83]/5 flex justify-between items-center max-w-7xl md:mx-auto">
        <a href="/" class="flex items-center gap-2 group">
            <div class="w-10 h-10 bg-[#592E83] rounded-xl flex items-center justify-center text-white font-black text-xl group-hover:scale-105 transition-transform shadow-md shadow-[#592E83]/30">
                AH
            </div>
            <span class="text-xl font-black tracking-tight text-[#230C33]">Amikom<span class="text-[#592E83]">EventHub</span></span>
        </a>

        <div class="hidden md:flex items-center gap-8 font-semibold text-sm">
            <a href="/" class="hover:text-[#592E83] transition {{ request()->routeIs('home') ? 'text-[#592E83] font-bold border-b-2 border-[#9984D4] pb-1' : 'text-slate-600' }}">Jelajahi Event</a>
            <a href="{{ route('organizer.index') }}" class="hover:text-[#592E83] transition {{ request()->routeIs('organizer.index') || request()->routeIs('organizer.show') ? 'text-[#592E83] font-bold border-b-2 border-[#9984D4] pb-1' : 'text-slate-600' }}">Daftar Penyelenggara</a>
            <a href="{{ route('ticket') }}" class="hover:text-[#592E83] transition {{ request()->routeIs('ticket') ? 'text-[#592E83] font-bold border-b-2 border-[#9984D4] pb-1' : 'text-slate-600' }}">Tiket Saya</a>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('organizer.register') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-[#CAA8F5]/30 text-[#592E83] hover:bg-[#CAA8F5]/60 rounded-xl font-bold text-xs transition border border-[#9984D4]/40">
                <span>+</span>
                <span>Daftar Pengelola</span>
            </a>

            @if(auth('web')->check())
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#230C33] hidden sm:inline-block">{{ auth('web')->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-[#CAA8F5]/30 text-[#230C33] rounded-xl text-xs font-bold hover:bg-[#CAA8F5]/60 transition">
                            Keluar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 bg-[#9984D4] text-white rounded-xl font-bold text-sm shadow-md shadow-[#9984D4]/30 hover:bg-[#8570c2] transition">
                    Masuk Akun
                </a>
            @endif
        </div>
    </nav>

    @yield('content')

    <footer class="bg-[#230C33] text-white py-16 px-6 mt-20 border-t-4 border-[#9984D4]">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="space-y-4 col-span-1 md:col-span-2">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-[#9984D4] rounded-xl flex items-center justify-center text-white font-black text-xl shadow-sm">
                        AH
                    </div>
                    <span class="text-2xl font-black text-white">AmikomEventHub</span>
                </div>
                <p class="max-w-sm text-[#CAA8F5] text-sm leading-relaxed">
                    Platform SaaS Ticketing Marketplace Multi-Tenant resmi untuk Universitas Amikom Yogyakarta dan Penyelenggara Event Profesional.
                </p>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Navigasi Utama</h4>
                <ul class="space-y-3 text-sm text-[#CAA8F5]">
                    <li><a href="/" class="hover:text-[#9984D4] transition">Beranda Event</a></li>
                    <li><a href="{{ route('ticket') }}" class="hover:text-[#9984D4] transition">Pesanan & E-Ticket Saya</a></li>
                    <li><a href="{{ route('organizer.register') }}" class="hover:text-[#9984D4] transition">Daftar Penyelenggara Event</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-[#9984D4] transition">Portal Login</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Kategori Event</h4>
                <ul class="space-y-3 text-sm text-[#CAA8F5]">
                    <li><a href="/" class="hover:text-[#9984D4] transition font-semibold">Semua Kategori</a></li>
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ url('/?category=' . $category->slug) }}" class="hover:text-[#9984D4] transition capitalize">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-8 mt-12 border-t border-[#592E83] text-center text-[#CAA8F5] text-xs">
            &copy; {{ date('Y') }} AmikomEventHub SaaS Platform. Built with Laravel 13 & Tailwind CSS.
        </div>
    </footer>

    @if(session('order_cancelled') || session('cancelled'))
        <!-- Pop Up Modal Notifikasi Pembatalan -->
        <div id="cancel-modal-backdrop" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#230C33]/70 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-[#CAA8F5] text-center relative transform transition-all scale-100">
                <button type="button" onclick="document.getElementById('cancel-modal-backdrop').remove()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-2 rounded-full hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-black text-[#230C33] mb-2">Pesanan Berhasil Dibatalkan!</h3>
                <p class="text-slate-600 text-sm mb-6 leading-relaxed">
                    {{ session('order_cancelled') ?? session('cancelled') }}
                </p>

                <button type="button" onclick="document.getElementById('cancel-modal-backdrop').remove()" class="w-full py-3.5 bg-[#9984D4] text-white rounded-2xl font-bold hover:bg-[#8570c2] active:scale-95 transition-all shadow-lg shadow-[#9984D4]/30">
                    Tutup Notifikasi
                </button>
            </div>
        </div>
    @endif

</body>

</html>