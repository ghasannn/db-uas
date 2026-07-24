@extends('layouts.app')
@section('title', 'Checkout - ' . $event->title)
@section('content')
<main class="max-w-3xl mx-auto px-6 py-20">
    <div class="mb-12">
        <a href="{{ route('events.show', $event->id) }}" class="text-[#592E83] font-bold flex items-center gap-2 mb-6 hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Event
        </a>
        <h1 class="text-4xl font-extrabold text-[#230C33]">Checkout</h1>
        <p class="text-slate-500 mt-2">Lengkapi data Anda untuk mendapatkan tiket.</p>
    </div>

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-xl font-bold">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-8">
        <!-- Summary Card -->
        <div class="bg-white rounded-3xl border border-[#CAA8F5]/40 p-8 shadow-sm">
            <h3 class="text-xl font-bold mb-6 border-b pb-4 text-[#230C33]">Pesanan Anda</h3>
            <div class="flex gap-6 items-start">
                <img src="{{ ($event->poster_path && Storage::disk('public')->exists($event->poster_path))
                  ? asset('storage/' . $event->poster_path)
                  : 'https://placehold.co/200x200' }}" alt="Event" class="w-24 h-24 rounded-2xl object-cover">
                <div>
                    <h4 class="font-extrabold text-lg text-[#230C33]">{{ $event->title }}</h4>
                    <p class="text-slate-500">{{ $event->date->format('d M Y') }} • {{ $event->location }}</p>
                    <p class="text-[#9984D4] font-bold mt-2">1 x {{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t space-y-3">
                <div class="flex justify-between text-slate-500">
                    <span>Harga Tiket</span>
                    <span>{{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Biaya Layanan</span>
                    <span>{{ ($event->price == 0 || $event->is_free) ? 'Gratis (Rp 0)' : 'Rp 5.000' }}</span>
                </div>
                <div class="flex justify-between text-2xl font-black mt-4 pt-4 border-t">
                    <span>Total Bayar</span>
                    <span class="text-[#9984D4]">{{ ($event->price == 0 || $event->is_free) ? 'Gratis' : 'Rp ' . number_format($event->price + 5000, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-3xl border border-[#CAA8F5]/40 p-8 shadow-sm">
            <h3 class="text-xl font-bold mb-6 italic text-[#592E83] underline underline-offset-8">📦 Data Pemesan
                (Tanpa Login)</h3>
            <form action="{{ route('checkout.store', $event->id) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Nama
                        Lengkap</label>
                    <input type="text" name="customer_name" placeholder="Masukkan nama sesuai identitas" class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-[#592E83]/10 focus:border-[#592E83] outline-none transition font-medium" required value="{{ old('customer_name') }}">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Email
                            Aktif</label>
                        <input type="email" name="customer_email" placeholder="contoh@gmail.com" class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-[#592E83]/10 focus:border-[#592E83] outline-none transition font-medium" required value="{{ old('customer_email') }}">
                        <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-tighter">*E-Ticket
                            akan dikirim ke email ini</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">No.
                            WhatsApp</label>
                        <input type="tel" name="customer_phone" placeholder="08xxxxxxx" class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-[#592E83]/10 focus:border-[#592E83] outline-none transition font-medium" required value="{{ old('customer_phone') }}">
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-[#9984D4] text-white rounded-2xl font-black text-xl shadow-xl shadow-[#9984D4]/30 hover:bg-[#8570c2] active:scale-95 transition-all">
                    {{ ($event->price == 0 || $event->is_free) ? 'Klaim Tiket Gratis' : 'Lanjut Pembayaran' }}
                </button>
                <p class="text-center text-xs text-slate-400">Dengan menekan tombol di atas, Anda menyetujui Syarat
                    & Ketentuan kami.</p>
            </form>
        </div>

    </div>
</main>
@endsection