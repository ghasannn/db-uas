@extends('layouts.admin')
@section('title', 'Kelola Partner - Admin')
@section('page_title', 'Manajemen Partner')
@section('page_subtitle', 'Kelola daftar instansi dan logo partner kerja sama')
@section('content')

<div class="p-8 bg-white rounded-3xl border border-slate-100 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form action="{{ route('admin.partners.index') }}" method="GET" class="flex gap-2 w-full sm:max-w-md">
            <input type="text" name="search" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-indigo-500" placeholder="Cari nama partner..." value="{{ $search ?? '' }}">
            <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition">
                Cari
            </button>
        </form>
        <a href="{{ route('admin.partners.create') }}" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition text-center text-sm">
            + Tambah Partner
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                    <th class="px-8 py-4 w-24">ID</th>
                    <th class="px-8 py-4">Nama Partner</th>
                    <th class="px-8 py-4">Logo</th>
                    <th class="px-8 py-4 text-center w-48">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-slate-600">
                @forelse($partners as $partner)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-8 py-5 font-bold text-slate-400">#{{ $partner->id }}</td>
                    <td class="px-8 py-5 font-black text-slate-900">{{ $partner->name }}</td>
                    <td class="px-8 py-5">
                        @if($partner->logo_url)
                            <img src="{{ asset('storage/' . $partner->logo_url) }}" alt="Logo" class="h-8 w-24 object-contain object-left">
                        @else
                            <span class="text-xs text-slate-400 italic">Tidak ada logo</span>
                        @endif
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('admin.partners.edit', $partner->id) }}" class="px-4 py-2 bg-amber-50 text-amber-600 font-bold rounded-xl text-xs hover:bg-amber-100 transition">
                                Edit
                            </a>
                            <form action="{{ route('admin.partners.destroy', $partner->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus partner ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-rose-50 text-rose-600 font-bold rounded-xl text-xs hover:bg-rose-100 transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-10 text-center text-slate-400 italic font-medium">
                        Belum ada data partner terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection