@extends('layouts.app')

@section('content')

<section class="max-w-7xl mx-auto px-6 py-16 flex flex-col md:flex-row items-center gap-12">
    <div class="flex-1 space-y-8">
        <span class="inline-block px-4 py-1.5 bg-[#CAA8F5]/40 text-[#592E83] border border-[#9984D4]/40 rounded-full text-sm font-bold uppercase tracking-wider">
            Multi-Tenant SaaS Ticketing Platform
        </span>
        <h1 class="text-5xl md:text-6xl font-black leading-tight text-[#230C33]">
            Temukan & Pesan <span class="text-[#9984D4]">Tiket Event</span> Kepanitiaan Amikom.
        </h1>
        <p class="text-lg text-slate-600 max-w-lg leading-relaxed">
            Marketplace event multi-organisasi resmi Amikom. Pesan tiket gratis & berbayar dengan cepat, aman, dan instan via Google SSO.
        </p>
        <div class="flex flex-wrap gap-4">
            <a href="#events" class="px-8 py-4 bg-[#9984D4] text-white rounded-2xl font-bold text-lg shadow-xl shadow-[#9984D4]/30 hover:bg-[#8570c2] hover:scale-105 transition-all">
                Mulai Jelajah Event
            </a>
            <a href="{{ route('organizer.register') }}" class="px-8 py-4 border-2 border-[#9984D4] text-[#592E83] rounded-2xl font-bold text-lg hover:bg-[#592E83] hover:text-white transition-all">
                + Daftar Penyelenggara
            </a>
        </div>
    </div>

    <div class="flex-1 relative">
        <div class="absolute -top-10 -left-10 w-64 h-64 bg-[#CAA8F5] rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-[#9984D4] rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        
        <div class="bg-[#592E83] text-white p-8 rounded-[2.5rem] shadow-2xl shadow-[#592E83]/30 relative z-10 space-y-6 border border-[#CAA8F5]/30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-[#9984D4] rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-md">
                    AH
                </div>
                <div>
                    <h3 class="font-black text-xl text-white">AmikomEventHub SaaS</h3>
                    <p class="text-xs text-[#CAA8F5]">Multi-Tenant Ticketing Ecosystem</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-[#CAA8F5]/30">
                <div class="p-4 bg-[#230C33]/70 rounded-2xl border border-[#CAA8F5]/20">
                    <p class="text-xs text-[#CAA8F5] font-bold uppercase">Fitur Wajib</p>
                    <p class="font-bold text-sm text-emerald-300 mt-1">✓ Google SSO & Multi-Tenant</p>
                </div>
                <div class="p-4 bg-[#230C33]/70 rounded-2xl border border-[#CAA8F5]/20">
                    <p class="text-xs text-[#CAA8F5] font-bold uppercase">Fitur Pilihan</p>
                    <p class="font-bold text-sm text-emerald-300 mt-1">✓ Lock Reserved & Free Bypass</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="events" class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-center mb-10">
        <h2 class="text-3xl md:text-4xl font-black mb-2 text-[#230C33]">Jelajahi Event Terbaru</h2>
        <p class="text-slate-600 font-medium">Temukan acara kepanitiaan, workshop, dan seminar kampus Amikom!</p>
    </div>

    <div class="flex flex-col items-center mb-12">
        <div class="flex flex-wrap justify-center gap-3 p-1.5 bg-[#CAA8F5]/20 rounded-2xl border border-[#CAA8F5]/40">
            <a href="{{ url('/') }}" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 {{ !request('category') ? 'bg-[#9984D4] text-white shadow-md' : 'text-[#592E83] hover:bg-white/60' }}">
                Semua Event
            </a>

            @foreach($categories as $cat)
            <a href="{{ url('/?category=' . $cat->slug) }}" class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 {{ request('category') == $cat->slug ? 'bg-[#9984D4] text-white shadow-md' : 'text-[#592E83] hover:bg-white/60' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($events as $event)
        <div class="group bg-white rounded-3xl border border-[#CAA8F5]/30 shadow-sm hover:shadow-2xl hover:border-[#9984D4] transition-all duration-300 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="relative overflow-hidden aspect-[3/4]">
                    <img src="{{ ($event->poster_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->poster_path))
                                 ? asset('storage/' . $event->poster_path)
                                 : 'https://placehold.co/400x500?text=' . urlencode($event->title) }}" 
                         alt="{{ $event->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <span class="px-3 py-1 bg-white/90 backdrop-blur rounded-lg text-xs font-bold uppercase text-[#592E83] shadow-sm">
                            {{ $event->category->name ?? 'Uncategorized' }}
                        </span>
                        @if($event->is_free || $event->price == 0)
                            <span class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold uppercase shadow-sm">
                                Tiket Gratis
                            </span>
                        @endif
                    </div>

                    <div class="absolute top-4 right-4 px-3 py-1 bg-amber-500 text-white rounded-lg text-xs font-bold shadow-sm">
                        ⭐ {{ $event->averageRating() }}
                    </div>
                </div>

                <div class="p-6 space-y-3">
                    @if($event->organization)
                        <a href="{{ route('organizer.show', $event->organization->slug) }}" class="text-xs font-bold text-[#592E83] hover:underline inline-block">
                            🏢 {{ $event->organization->name }}
                        </a>
                    @else
                        <span class="text-xs font-bold text-slate-400">🏢 Amikom Event Hub</span>
                    @endif

                    <h3 class="text-xl font-bold text-[#230C33] group-hover:text-[#592E83] transition line-clamp-2">
                        <a href="{{ route('events.show', $event->id) }}">
                            {{ $event->title }}
                        </a>
                    </h3>

                    <div class="flex items-center gap-2 text-slate-500 text-xs font-medium">
                        <svg class="w-4 h-4 text-[#592E83]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }} WIB</span>
                    </div>
                </div>
            </div>

            <div class="p-6 pt-0 border-t border-slate-100 mt-4 flex justify-between items-center">
                <div>
                    <span class="text-xs text-slate-400 font-bold block">Harga Tiket</span>
                    <span class="text-xl font-black text-[#9984D4]">
                        {{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}
                    </span>
                </div>
                
                <a href="{{ route('events.show', $event->id) }}" class="px-5 py-2.5 bg-[#CAA8F5]/30 text-[#592E83] border border-[#9984D4]/30 rounded-xl font-bold text-sm hover:bg-[#592E83] hover:text-white transition">
                    Lihat Detail
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-slate-100">
            <p class="text-slate-400 text-lg">Belum ada event tersedia untuk kategori ini.</p>
        </div>
        @endforelse
    </div>
</section>

@endsection