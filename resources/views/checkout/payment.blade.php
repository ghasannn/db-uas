@extends('layouts.app')
@section('title', 'Pembayaran - ' . $transaction->event->title)
@section('content')
<main class="max-w-3xl mx-auto px-6 py-20 text-center">
    <div class="bg-white rounded-3xl border border-slate-200 p-12 shadow-sm inline-block w-full max-w-md">
        @if(session('warning'))
            <div class="mb-6 p-4 bg-amber-100 text-amber-800 rounded-2xl font-bold text-sm text-left">
                ⚠️ {{ session('warning') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-rose-100 text-rose-800 rounded-2xl font-bold text-sm text-left">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if(in_array(strtolower($transaction->status), ['cancelled', 'expired', 'failed']))
            <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-black text-[#230C33] mb-2">Pemesanan Dibatalkan</h2>
            <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                Pesanan dengan kode <strong class="text-slate-800">{{ $transaction->order_id }}</strong> telah dibatalkan atau kadaluarsa. Stok tiket telah dikembalikan.
            </p>
            <a href="{{ route('events.show', $transaction->event_id) }}" class="block w-full py-4 bg-[#592E83] text-white rounded-xl font-bold hover:bg-[#432263] transition">
                Kembali ke Event
            </a>
        @else
            <div class="w-20 h-20 bg-[#CAA8F5]/30 text-[#592E83] rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-black text-[#230C33] mb-2">Selesaikan Pembayaran</h2>
            <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                Pesanan Anda dengan kode <strong class="text-[#230C33]">{{ $transaction->order_id }}</strong> telah siap. Silakan klik tombol di bawah untuk membuka opsi loket pembayaran resmi.
            </p>
            
            <div class="bg-[#f7f6fc] border border-[#CAA8F5]/40 rounded-2xl p-4 mb-8 text-left">
                <div class="flex justify-between text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">
                    <span>Total Tagihan</span>
                </div>
                <div class="text-3xl font-black text-[#9984D4]">
                    Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                </div>
            </div>

            <div class="space-y-3">
                <button id="pay-button" class="w-full py-4 bg-[#9984D4] text-white rounded-xl font-bold hover:bg-[#8570c2] shadow-lg shadow-[#9984D4]/30 active:scale-95 transition-all text-lg">
                    Munculkan Jendela Pembayaran
                </button>
                <a href="{{ route('checkout.success', $transaction->order_id) }}" class="block w-full py-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl font-bold text-sm hover:bg-emerald-100 transition">
                    ✓ Saya Sudah Bayar / Cek Status Pembayaran
                </a>
                
                <form action="{{ route('checkout.cancel', $transaction->order_id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pemesanan tiket ini? Stok tiket akan dikembalikan.');">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl font-bold text-sm hover:bg-rose-100 active:scale-95 transition-all">
                        ✕ Batalkan Pemesanan
                    </button>
                </form>
            </div>
        @endif
    </div>
</main>

@if(!in_array(strtolower($transaction->status), ['cancelled', 'expired', 'failed']))
{{-- Menyisipkan library Snap JS Midtrans Sandbox --}}
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.onclick = function (e) {
            e.preventDefault();
            
            // Memicu popup snap window dengan token yang telah di-generate dari controller
            window.snap.pay('{{ $transaction->snap_token }}', {
                onSuccess: function(result){
                    /* Pembayaran sukses -> Pindah ke halaman terima kasih */
                    window.location.href = "{{ route('checkout.success', $transaction->order_id) }}";
                },
                onPending: function(result){
                    /* Pending (dapatkan kode VA / QRIS) -> Tetap di halaman memilih pembayaran */
                    console.log('Payment pending', result);
                },
                onError: function(result){
                    alert("Pembayaran belum berhasil diselesaikan.");
                },
                onClose: function(){
                    /* User menutup modal (klik X) -> Biarkan user tetap di halaman memilih pembayaran */
                    console.log('User closed Midtrans popup window');
                }
            });
        };

        // Auto-trigger klik tombol ketika halaman selesai di-load browser
        window.onload = function() {
            payButton.click();
        }
    }
</script>
@endif
@endsection