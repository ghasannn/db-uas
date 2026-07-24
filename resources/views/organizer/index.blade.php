@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-12 space-y-12">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl relative overflow-hidden">
        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-indigo-200 border border-white/20">
                Direktori Penyelenggara
            </span>
            <h1 class="text-3xl md:text-5xl font-black leading-tight">
                Daftar Penyelenggara Event Resmi
            </h1>
            <p class="text-indigo-200 text-base md:text-lg leading-relaxed">
                Temukan berbagai organisasi, UKM, dan kepanitiaan di AmikomEventHub. Lihat rekam jejak event yang telah diselenggarakan serta ulasan langsung dari para peserta.
            </p>
        </div>
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -left-12 -top-12 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Organizers Grid -->
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Semua Penyelenggara Terdaftar</h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">{{ $organizers->total() }} Organisasi Terverifikasi</p>
            </div>
            <a href="{{ route('organizer.register') }}" class="px-5 py-3 bg-indigo-600 text-white font-bold rounded-2xl text-xs hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 flex items-center gap-2 self-start sm:self-auto">
                <span>+</span>
                <span>Tambah / Daftar Penyelenggara Baru</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($organizers as $org)
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 flex flex-col justify-between hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="space-y-6">
                        <!-- Top Info -->
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shrink-0 shadow-md">
                                {{ strtoupper(substr($org->name, 0, 2)) }}
                            </div>
                            <div class="space-y-1 overflow-hidden">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-lg text-slate-900 truncate" title="{{ $org->name }}">{{ $org->name }}</h3>
                                </div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full inline-block">
                                    ✓ Verified Organizer
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-slate-600 text-xs md:text-sm line-clamp-2 leading-relaxed">
                            {{ $org->description ?? 'Penyelenggara event resmi terdaftar di platform AmikomEventHub.' }}
                        </p>

                        <!-- Rating & Events Stats -->
                        <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <div>
                                <p class="text-xs text-slate-400 font-semibold uppercase">Total Event</p>
                                <p class="text-lg font-black text-indigo-600">{{ $org->events_count }} Event</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 font-semibold uppercase">Rating</p>
                                <p class="text-lg font-black text-amber-500">⭐ {{ $org->averageRating() }} / 5</p>
                            </div>
                        </div>

                        <!-- List Event Preview -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Event Terbaru / Diselenggarakan:</h4>
                            @if(count($org->events) > 0)
                                <div class="space-y-2">
                                    @foreach($org->events as $ev)
                                        <a href="{{ route('events.show', $ev->id) }}" class="block p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 transition group">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-bold text-xs text-slate-800 group-hover:text-indigo-600 line-clamp-1">
                                                    {{ $ev->title }}
                                                </span>
                                                @if($ev->date < now())
                                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-200 text-slate-600 rounded-full shrink-0">Selesai</span>
                                                @else
                                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full shrink-0">Mendatang</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-400 mt-1">📅 {{ \Carbon\Carbon::parse($ev->date)->format('d M Y') }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-400 italic bg-slate-50 p-3 rounded-xl">Belum ada event yang dipublikasikan.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-6 mt-6 border-t border-slate-100">
                        <a href="{{ route('organizer.show', $org->slug) }}" class="w-full py-3 px-4 bg-slate-900 text-white font-bold rounded-2xl text-xs flex items-center justify-center gap-2 hover:bg-indigo-600 transition shadow-md">
                            <span>Lihat Profil & Semua Event</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-3xl border border-slate-100 space-y-4">
                    <div class="text-4xl">🏛️</div>
                    <h3 class="text-lg font-bold text-slate-700">Belum ada penyelenggara event</h3>
                    <p class="text-slate-400 text-sm">Daftarkan organisasi Anda untuk menjadi yang pertama menyelenggarakan event!</p>
                </div>
            @endforelse
        </div>

        <div class="pt-4">
            {{ $organizers->links() }}
        </div>
    </div>

    <!-- Call to action: Join as Organizer -->
    <div class="bg-indigo-50 border border-indigo-100 rounded-[2.5rem] p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <h3 class="text-2xl font-black text-slate-900">Ingin Menjadi Penyelenggara Event?</h3>
            <p class="text-slate-600 text-sm max-w-xl">
                Daftarkan organisasi atau kepanitiaan Anda di AmikomEventHub dan mulai publikasikan event Anda dengan kemudahan sistem ticketing digital.
            </p>
        </div>
        <a href="{{ route('organizer.register') }}" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold text-sm hover:bg-indigo-700 transition shrink-0 shadow-lg shadow-indigo-200">
            Daftar Sebagai Penyelenggara &rarr;
        </a>
    </div>
</main>
@endsection
