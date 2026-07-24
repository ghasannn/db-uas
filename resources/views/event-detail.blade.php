@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
        <div class="lg:col-span-1">
            <div class="sticky top-32">
                <img src="{{ ($event->poster_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->poster_path))
                              ? asset('storage/' . $event->poster_path)
                              : 'https://placehold.co/400x600?text=' . urlencode($event->title) }}" 
                     alt="{{ $event->title }}" 
                     class="w-full rounded-[2.5rem] shadow-2xl border-8 border-white object-cover aspect-[3/4]">
                     
                <div class="mt-8 p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="font-bold mb-4">Penyelenggara</h4>
                    @if($event->organization)
                        <a href="{{ route('organizer.show', $event->organization->slug) }}" class="flex items-center gap-4 group hover:opacity-80 transition">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-lg">
                                {{ strtoupper(substr($event->organization->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 group-hover:text-indigo-600 transition">{{ $event->organization->name }}</p>
                                <p class="text-xs text-slate-500">Verified Organizer &bull; ⭐ {{ $event->organization->averageRating() }} / 5</p>
                            </div>
                        </a>
                    @else
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                                AE
                            </div>
                            <div>
                                <p class="font-bold text-slate-800">Amikom Event Hub</p>
                                <p class="text-xs text-slate-500">Verified Organizer</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-12">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <span class="px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold uppercase tracking-wider">
                        {{ $event->category->name ?? 'Uncategorized' }}
                    </span>

                    @if($event->date < now())
                        <span class="px-4 py-1.5 bg-amber-100 text-amber-800 rounded-full text-sm font-bold uppercase">
                            Event Telah Selesai
                        </span>
                    @endif

                    @if($event->is_free || $event->price == 0)
                        <span class="px-4 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-sm font-bold uppercase">
                            Tiket Gratis
                        </span>
                    @endif
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black leading-tight">
                    {{ $event->title }}
                </h1>
                
                <div class="flex flex-wrap gap-6 text-slate-500 font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }} WIB</span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>
            </div>

            <div class="prose prose-slate max-w-none">
                <h3 class="text-2xl font-bold mb-4">Deskripsi Event</h3>
                <div class="text-lg text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $event->description ?? 'Tidak ada deskripsi untuk event ini.' }}
                </div>
            </div>

            <div class="bg-[#592E83] rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-[#592E83]/30 relative overflow-hidden border border-[#CAA8F5]/30">
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                    <div>
                        <p class="text-[#CAA8F5] font-bold uppercase tracking-widest text-sm mb-2">Harga Tiket</p>
                        
                        <h2 class="text-4xl md:text-5xl font-black">
                            {{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}
                            @if($event->price > 0 && !$event->is_free)
                                <span class="text-lg font-medium text-[#CAA8F5]">/ orang</span>
                            @endif
                        </h2>
                        
                        <p class="mt-4 text-indigo-100 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Sisa stok: <span class="font-bold underline text-[#CAA8F5]">{{ $event->availableStock() }} Tiket lagi!</span>
                        </p>
                    </div>
                    <div>
                        @if($event->date < now())
                            <span class="inline-block px-8 py-4 bg-[#230C33] text-slate-300 rounded-2xl font-bold text-lg">
                                Event Berakhir
                            </span>
                        @elseif($event->availableStock() <= 0)
                            <span class="inline-block px-8 py-4 bg-rose-700 text-white rounded-2xl font-bold text-lg">
                                Tiket Habis
                            </span>
                        @else
                            <a href="{{ url('checkout/' . $event->id) }}"
                                class="inline-block px-10 py-5 bg-[#9984D4] text-white rounded-2xl font-black text-xl hover:bg-[#8570c2] hover:scale-105 transition-all shadow-xl shadow-[#9984D4]/40">
                                {{ ($event->price == 0 || $event->is_free) ? 'Klaim Tiket Gratis' : 'Pesan Sekarang' }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white opacity-10 rounded-full"></div>
                <div class="absolute -left-10 -top-10 w-32 h-32 bg-[#CAA8F5] opacity-20 rounded-full"></div>
            </div>

            <!-- Ulasan & Rating Section -->
            <div class="space-y-6 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-[#230C33]">Ulasan & Rating Pembeli</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 font-black text-2xl">⭐ {{ $event->averageRating() }}</span>
                        <span class="text-slate-400">({{ $event->reviews->count() }} ulasan)</span>
                    </div>
                </div>

                <!-- Review Form for verified buyers of completed events -->
                @auth
                    @if($event->date < now())
                        @php
                            $userHasPaidTicket = \App\Models\Transaction::where('event_id', $event->id)
                                ->where(function($q) {
                                    $q->where('user_id', auth()->id())
                                      ->orWhere('customer_email', auth()->user()->email);
                                })
                                ->whereIn('status', ['paid', 'success', 'settlement', 'free_claimed'])
                                ->exists();

                            $alreadyReviewed = \App\Models\Review::where('user_id', auth()->id())
                                ->where('event_id', $event->id)
                                ->exists();
                        @endphp

                        @if($userHasPaidTicket && !$alreadyReviewed)
                            <div class="p-6 bg-[#f7f6fc] rounded-2xl border border-[#CAA8F5]/40">
                                <h4 class="font-bold text-[#230C33] mb-2">Beri Ulasan untuk Event Ini</h4>
                                <form action="{{ route('events.reviews.store', $event->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Rating (Bintang 1 - 5)</label>
                                        <select name="rating" required class="w-full md:w-48 px-4 py-2 border border-[#CAA8F5] rounded-xl bg-white focus:ring-2 focus:ring-[#592E83]">
                                            <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                                            <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                            <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                            <option value="2">⭐⭐ (2 - Kurang)</option>
                                            <option value="1">⭐ (1 - Buruk)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Komentar / Testimoni</label>
                                        <textarea name="comment" rows="3" placeholder="Bagaimana pengalaman Anda mengikuti event ini?" class="w-full px-4 py-2 border border-[#CAA8F5] rounded-xl bg-white focus:ring-2 focus:ring-[#592E83]"></textarea>
                                    </div>
                                    <button type="submit" class="px-6 py-2.5 bg-[#9984D4] text-white font-bold rounded-xl hover:bg-[#8570c2] transition shadow-md shadow-[#9984D4]/20">
                                        Kirim Ulasan
                                    </button>
                                </form>
                            </div>
                        @elseif($alreadyReviewed)
                            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm font-medium">
                                ✓ Anda sudah memberikan ulasan untuk event ini. Terima kasih!
                            </div>
                        @endif
                    @endif
                @endauth

                <!-- Reviews list -->
                <div class="space-y-4">
                    @forelse($event->reviews as $review)
                        <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center font-bold text-indigo-600 text-xs">
                                        {{ strtoupper(substr($review->user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-slate-800">{{ $review->user->name ?? 'Pengguna' }}</p>
                                        <p class="text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <span class="text-amber-500 font-bold text-sm">
                                    {{ str_repeat('⭐', $review->rating) }}
                                </span>
                            </div>
                            @if($review->comment)
                                <p class="text-slate-600 text-sm mt-2">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-slate-500 text-sm italic">Belum ada ulasan untuk event ini.</p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-xl font-bold">Kebijakan Tiket</h3>
                <ul class="space-y-3 text-slate-500">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        E-Ticket akan dikirimkan otomatis setelah pemesanan dikonfirmasi.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Tiket dapat discan di pintu masuk (Check-in).
                    </li>
                </ul>
            </div>
        </div>
    </main>
@endsection