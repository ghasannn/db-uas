@extends('layouts.app')

@section('content')
<main class="max-w-6xl mx-auto px-6 py-12 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-[#230C33]">Tiket & Pesanan Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar transaksi E-Ticket Anda di AmikomEventHub</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-100 text-emerald-800 rounded-2xl font-bold text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-100 text-rose-800 rounded-2xl font-bold text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($transactions as $trx)
            <div class="p-6 bg-white rounded-3xl border border-[#CAA8F5]/40 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono text-slate-400 font-bold">#{{ $trx->order_id }}</span>
                        @if($trx->status === 'free_claimed')
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">Gratis (Claimed)</span>
                        @elseif(in_array($trx->status, ['paid', 'success', 'settlement']))
                            <span class="px-3 py-1 bg-[#CAA8F5]/40 text-[#592E83] rounded-full text-xs font-bold border border-[#9984D4]/40">Lunas / Valid Ticket</span>
                        @elseif($trx->status === 'reserved')
                            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">Reserved (Pending Bayar)</span>
                        @else
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">{{ strtoupper($trx->status) }}</span>
                        @endif
                    </div>

                    <h3 class="font-bold text-lg text-[#230C33]">
                        {{ $trx->event->title ?? 'Event Amikom' }}
                    </h3>

                    <p class="text-xs text-slate-500">
                        Penyelenggara: <span class="font-semibold text-slate-700">{{ $trx->organization->name ?? ($trx->event->organization->name ?? 'Amikom Event Hub') }}</span>
                        &bull; Tanggal Acara: <span class="font-semibold text-slate-700">{{ $trx->event ? \Carbon\Carbon::parse($trx->event->date)->format('d M Y, H:i') : '-' }}</span>
                    </p>
                </div>

                <div class="flex flex-col md:flex-row items-start md:items-center gap-4 w-full md:w-auto">
                    <div class="text-left md:text-right">
                        <div class="text-xs text-slate-400 font-bold">Total Pembayaran</div>
                        <div class="text-lg font-black text-[#9984D4]">
                            {{ $trx->total_price == 0 ? 'Gratis' : 'Rp ' . number_format($trx->total_price, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        @if($trx->isPaid())
                            <a href="{{ route('ticket.show', $trx->order_id) }}" class="px-4 py-2.5 bg-[#9984D4] text-white rounded-xl text-xs font-bold hover:bg-[#8570c2] transition shadow-sm text-center flex-1 md:flex-none">
                                Lihat E-Ticket
                            </a>
                        @elseif(in_array($trx->status, ['reserved', 'pending']))
                            <a href="{{ route('checkout.payment', $trx->order_id) }}" class="px-4 py-2.5 bg-[#9984D4] text-white rounded-xl text-xs font-bold hover:bg-[#8570c2] transition shadow-sm text-center flex-1 md:flex-none">
                                Bayar Sekarang
                            </a>
                            <form action="{{ route('checkout.cancel', $trx->order_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?');" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold hover:bg-rose-100 transition text-center">
                                    Batalkan
                                </button>
                            </form>
                        @else
                            <span class="px-3 py-2 bg-slate-100 text-slate-500 rounded-xl text-xs font-bold">
                                {{ strtoupper($trx->status) }}
                            </span>
                        @endif

                        @if($trx->event && $trx->event->date < now() && in_array($trx->status, ['paid', 'success', 'settlement', 'free_claimed']))
                            @php
                                $alreadyReviewed = \App\Models\Review::where('user_id', auth()->id())
                                    ->where('event_id', $trx->event_id)
                                    ->exists();
                            @endphp

                            @if(!$alreadyReviewed)
                                <a href="{{ route('events.show', $trx->event_id) }}#reviews" class="px-4 py-2.5 bg-amber-500 text-white rounded-xl text-xs font-bold hover:bg-amber-600 transition shadow-sm text-center flex-1 md:flex-none">
                                    ⭐ Beri Ulasan
                                </a>
                            @else
                                <span class="px-3 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold border border-emerald-200">
                                    ✓ Sudah Diulas
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white rounded-3xl border border-slate-100 text-slate-400 italic">
                Anda belum memiliki riwayat pesanan tiket.
            </div>
        @endforelse
    </div>
</main>
@endsection
