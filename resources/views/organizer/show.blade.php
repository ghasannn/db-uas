@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-12 space-y-12">

    <!-- Organizer Profile Header -->
    <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-100 shadow-xl flex flex-col md:flex-row items-center md:items-start gap-8">
        <div class="w-24 h-24 bg-indigo-600 text-white rounded-3xl flex items-center justify-center text-3xl font-black shrink-0 shadow-lg">
            {{ strtoupper(substr($organization->name, 0, 2)) }}
        </div>

        <div class="space-y-3 text-center md:text-left flex-1">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                <h1 class="text-3xl md:text-4xl font-black text-slate-900">{{ $organization->name }}</h1>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-bold text-xs rounded-full uppercase">
                    Verified Tenant
                </span>
            </div>

            <p class="text-slate-600 text-base max-w-2xl">
                {{ $organization->description ?? 'Penyelenggara event resmi terdaftar di platform AmikomEventHub.' }}
            </p>

            <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 text-sm pt-2">
                <div class="flex items-center gap-2">
                    <span class="text-amber-500 font-black text-2xl">⭐ {{ $averageRating }}</span>
                    <span class="text-slate-500 font-medium">/ 5.0 ({{ $reviews->total() }} Ulasan)</span>
                </div>
                <div class="text-slate-400">|</div>
                <div class="font-bold text-slate-700">
                    {{ $events->total() }} Total Event
                </div>
            </div>
        </div>
    </div>

    <!-- Events by this Organizer -->
    <div class="space-y-6">
        <h2 class="text-2xl font-black text-slate-900">Event oleh {{ $organization->name }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($events as $event)
                <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col hover:shadow-xl transition">
                    <img src="{{ ($event->poster_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->poster_path))
                                  ? asset('storage/' . $event->poster_path)
                                  : 'https://placehold.co/400x300?text=' . urlencode($event->title) }}" 
                         alt="{{ $event->title }}" class="w-full h-48 object-cover">
                    
                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase">
                                    {{ $event->category->name ?? 'Event' }}
                                </span>
                                @if($event->date < now())
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold uppercase">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase">
                                        Mendatang
                                    </span>
                                @endif
                            </div>
                            <h3 class="font-bold text-lg text-slate-900 mt-2 line-clamp-2">
                                <a href="{{ route('events.show', $event->id) }}" class="hover:text-indigo-600 transition">
                                    {{ $event->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 mt-2">
                                📅 {{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div class="font-black text-slate-900 text-lg">
                                {{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('events.show', $event->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition">
                                Detail &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 text-slate-400 italic bg-white rounded-3xl border border-slate-100">
                    Belum ada event publik yang diselenggarakan oleh {{ $organization->name }}.
                </div>
            @endforelse
        </div>

        <div>
            {{ $events->links() }}
        </div>
    </div>

    <!-- Ratings & Testimonials Section -->
    <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-100 shadow-sm space-y-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-100 pb-6">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Ulasan & Reputation Pembeli</h2>
                <p class="text-slate-500 text-sm">Testimoni langsung dari peserta yang telah mengikuti event {{ $organization->name }}</p>
            </div>
            <div class="flex items-center gap-3 bg-amber-50 px-6 py-3 rounded-2xl border border-amber-200">
                <span class="text-3xl font-black text-amber-500">⭐ {{ $averageRating }}</span>
                <span class="text-xs font-bold text-amber-900">Rata-rata Rating</span>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($reviews as $review)
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($review->user->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ $review->user->name ?? 'Pembeli Tiket' }}</h4>
                                <p class="text-xs text-slate-400">Event: <span class="font-semibold text-slate-600">{{ $review->event->title ?? '-' }}</span> &bull; {{ $review->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-amber-500 text-sm font-bold">
                            {{ str_repeat('⭐', $review->rating) }} ({{ $review->rating }}/5)
                        </div>
                    </div>

                    @if($review->comment)
                        <p class="text-slate-700 text-sm leading-relaxed pl-13">
                            "{{ $review->comment }}"
                        </p>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 italic">
                    Belum ada ulasan untuk {{ $organization->name }}.
                </div>
            @endforelse
        </div>

        <div>
            {{ $reviews->links() }}
        </div>
    </div>
</main>
@endsection
