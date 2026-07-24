@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard Ringkasan')

@section('content')
@if(auth('admin')->check() && auth('admin')->user()->isOrganizer() && !auth('admin')->user()->isSuperadmin())
    @php
        $org = auth('admin')->user()->organization;
    @endphp
    @if($org && $org->status === 'pending')
        <div class="mb-8 p-6 bg-amber-50 border-2 border-amber-300 rounded-3xl text-amber-900 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
            <div class="space-y-1">
                <div class="flex items-center gap-2 font-black text-amber-800 text-lg">
                    <span>⏳</span> Status Pendaftaran: PENDING ACC SUPERADMIN
                </div>
                <p class="text-xs md:text-sm text-amber-700 leading-relaxed">
                    Organisasi <strong>{{ $org->name }}</strong> saat ini masih dalam proses peninjauan dan menunggu persetujuan (ACC) dari Superadmin. Anda belum dapat mempublikasikan event baru hingga disetujui.
                </p>
            </div>
            <span class="px-4 py-2 bg-amber-200 text-amber-900 rounded-xl text-xs font-extrabold uppercase shrink-0">
                Menunggu ACC
            </span>
        </div>
    @elseif($org && $org->status === 'approved')
        <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs md:text-sm font-bold">
                <span>✅</span> Organisasi <strong>{{ $org->name }}</strong> telah TERVERIFIKASI & DISETUJUI oleh Superadmin.
            </div>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold uppercase">APPROVED</span>
        </div>
    @endif
@endif
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-slate-400 text-sm font-bold uppercase mb-1">Total Pendapatan</p>
        <h3 class="text-2xl font-black">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
        </div>
        <p class="text-slate-400 text-sm font-bold uppercase mb-1">Tiket Terjual</p>
        <h3 class="text-2xl font-black">{{ number_format($ticketsSold ?? 0, 0, ',', '.') }}</h3>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-slate-400 text-sm font-bold uppercase mb-1">Event Aktif</p>
        <h3 class="text-2xl font-black">{{ $activeEvents ?? 0 }} Event</h3>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <p class="text-slate-400 text-sm font-bold uppercase mb-1">Pesanan Pending</p>
        <h3 class="text-2xl font-black">{{ $pendingOrders ?? 0 }} Pesanan</h3>
    </div>
</div>

@if(auth('admin')->check() && auth('admin')->user()->isSuperadmin())
<!-- Superadmin Quick Access Cards -->
<div class="mb-10">
    <h3 class="font-black text-xl mb-4 text-slate-800">Kelola Platform (Superadmin)</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.organizers.index') }}" class="group bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0v-4a2 2 0 012-2h2a2 2 0 012 2v4"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-indigo-600 transition">Kelola Organizer</h4>
                    <p class="text-xs text-slate-400 font-medium">{{ $totalOrganizations ?? 0 }} Penyelenggara @if(($pendingOrganizations ?? 0) > 0) <span class="text-amber-500 font-bold">({{ $pendingOrganizations }} Pending)</span> @endif</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <a href="{{ route('admin.categories.index') }}" class="group bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-purple-600 transition">Kelola Kategori</h4>
                    <p class="text-xs text-slate-400 font-medium">{{ $totalCategories ?? 0 }} Kategori Event</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-300 group-hover:text-purple-600 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <a href="{{ route('admin.partners.index') }}" class="group bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-emerald-600 transition">Kelola Partner</h4>
                    <p class="text-xs text-slate-400 font-medium">{{ $totalPartners ?? 0 }} Partner Terdaftar</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-slate-300 group-hover:text-emerald-600 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
</div>
@endif

<!-- Latest Sales Table -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="p-8 border-b flex justify-between items-center">
        <h3 class="font-black text-xl">Transaksi Terakhir</h3>
        <a href="{{ route('admin.transactions.index') }}" class="text-indigo-600 font-bold hover:underline">Lihat Semua</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                <tr>
                    <th class="px-8 py-4">Tgl Transaksi</th>
                    <th class="px-8 py-4">Pembeli</th>
                    <th class="px-8 py-4">Event</th>
                    <th class="px-8 py-4">Status</th>
                    <th class="px-8 py-4 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y border-t">
                @forelse($recentTransactions as $trx)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-8 py-6 text-sm text-slate-600">
                        {{ $trx->created_at?->format('d M y - H:i') ?? '-' }}<br>
                        <span class="text-xs text-slate-400">{{ $trx->order_id ?? '-' }}</span>
                    </td>
                    <td class="px-8 py-6">
                        <p class="font-bold text-sm truncate">{{ $trx->customer_name ?? '-' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $trx->customer_email ?? '-' }}</p>
                    </td>
                    <td class="px-8 py-6 font-medium text-slate-600 truncate">
                        {{ $trx->event->title ?? '-' }}
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap">
                        @if(in_array($trx->status, ['settlement', 'success']))
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold uppercase">Success</span>
                        @elseif($trx->status === 'pending')
                            <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-lg text-xs font-bold uppercase">Pending</span>
                        @else
                            <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-lg text-xs font-bold uppercase">{{ ucfirst($trx->status ?? 'unknown') }}</span>
                        @endif
                    </td>
                    <td class="px-8 py-6 font-black text-indigo-600 whitespace-nowrap text-right">
                        Rp {{ number_format($trx->total_price ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-10 text-center text-slate-500">Belum ada transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection