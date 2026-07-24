@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-10 space-y-8">

    <!-- Header Organization Banner -->
    <div class="bg-indigo-900 rounded-[2.5rem] p-8 md:p-10 text-white shadow-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-indigo-800 text-indigo-200 rounded-full text-xs font-bold uppercase">
                    Status: {{ strtoupper($organization->status) }}
                </span>
                @if($organization->status === 'approved')
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-xs font-bold">
                        ✓ Verified Active Tenant
                    </span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-black mt-2">{{ $organization->name }}</h1>
            <p class="text-indigo-200 text-sm mt-1 max-w-xl">{{ $organization->description ?? 'Dashboard Penyelenggara Event Amikom' }}</p>
        </div>

        <div>
            @if($organization->status === 'approved')
                <a href="{{ route('admin.events.create') }}" class="px-6 py-3.5 bg-white text-indigo-900 rounded-2xl font-black text-sm hover:bg-indigo-50 transition shadow-lg inline-block">
                    + Buat Event Baru
                </a>
            @else
                <button disabled class="px-6 py-3.5 bg-slate-700 text-slate-400 rounded-2xl font-bold text-sm cursor-not-allowed">
                    🔒 Menunggu Approval Superadmin
                </button>
            @endif
        </div>
    </div>

    @if($organization->status === 'pending')
        <div class="p-6 bg-amber-50 rounded-3xl border border-amber-200 text-amber-900 flex items-start gap-4">
            <span class="text-3xl">⏳</span>
            <div>
                <h4 class="font-bold text-lg">Pendaftaran Organisasi Sedang Diproses</h4>
                <p class="text-sm text-amber-800 mt-1">Akun organisasi Anda saat ini berstatus <strong>PENDING</strong>. Superadmin akan meninjau pendaftaran Anda. Setelah disetujui, Anda akan dapat membuat dan mempublikasikan tiket event.</p>
            </div>
        </div>
    @endif

    <!-- Stat Metrics Grid (Scoped to Tenant) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan Tiket</p>
            <h3 class="text-3xl font-black text-slate-900 mt-2">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </h3>
            <p class="text-xs text-emerald-600 mt-2 font-semibold">✓ Scoped ke {{ $organization->name }}</p>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tiket Terjual</p>
            <h3 class="text-3xl font-black text-indigo-600 mt-2">
                {{ $ticketsSold }} Tiket
            </h3>
            <p class="text-xs text-slate-500 mt-2 font-medium">Dari seluruh event aktif</p>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Event</p>
            <h3 class="text-3xl font-black text-slate-900 mt-2">
                {{ $events->count() }} Event
            </h3>
            <p class="text-xs text-slate-500 mt-2 font-medium">Event yang terdaftar</p>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Rating Organisasi</p>
            <h3 class="text-3xl font-black text-amber-500 mt-2 flex items-center gap-2">
                ⭐ {{ $averageRating }} <span class="text-xs font-normal text-slate-400">/ 5.0</span>
            </h3>
            <p class="text-xs text-slate-500 mt-2 font-medium">Dari {{ $totalReviews }} ulasan pembeli</p>
        </div>
    </div>

    <!-- Tenant Events List -->
    <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900">Daftar Event Organisasi Anda</h3>
            <a href="{{ route('organizer.show', $organization->slug) }}" target="_blank" class="text-sm font-bold text-indigo-600 hover:underline">
                Lihat Profil Publik Organisasi &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Event</th>
                        <th class="py-4 px-4">Tanggal</th>
                        <th class="py-4 px-4">Harga</th>
                        <th class="py-4 px-4">Stok (Tersedia / Total)</th>
                        <th class="py-4 px-4">Terjual</th>
                        <th class="py-4 px-4">Rating</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $event->title }}</div>
                                <div class="text-xs text-slate-400">{{ $event->category->name ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-slate-700">
                                <span class="font-bold text-indigo-600">{{ $event->availableStock() }}</span> / {{ $event->quota > 0 ? $event->quota : $event->stock }}
                            </td>
                            <td class="py-4 px-4 font-bold text-emerald-600">
                                {{ $event->sold_count }}
                            </td>
                            <td class="py-4 px-4 font-bold text-amber-500">
                                ⭐ {{ $event->averageRating() }}
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                <a href="{{ route('events.show', $event->id) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
                                    Lihat
                                </a>
                                <a href="{{ route('admin.events.edit', $event->id) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-bold hover:bg-indigo-100 transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 italic">
                                Belum ada event yang dibuat oleh organisasi ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
