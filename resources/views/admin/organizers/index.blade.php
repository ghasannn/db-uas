@extends('layouts.admin')
@section('title', 'Manajemen Organisasi - Admin')
@section('page_title', 'Manajemen Organisasi (Tenants)')
@section('page_subtitle', 'Setujui, tolak, atau tangguhkan akun kepanitiaan / organisasi yang terdaftar.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold uppercase">
                Superadmin Controls
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.organizers.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ !$status ? 'bg-indigo-600 text-white' : 'bg-white border text-slate-700' }}">
                Semua
            </a>
            <a href="{{ route('admin.organizers.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $status === 'pending' ? 'bg-amber-500 text-white' : 'bg-white border text-slate-700' }}">
                Pending
            </a>
            <a href="{{ route('admin.organizers.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-xl text-xs font-bold {{ $status === 'approved' ? 'bg-emerald-600 text-white' : 'bg-white border text-slate-700' }}">
                Approved
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Organisasi</th>
                        <th class="py-4 px-4">Owner / Penanggung Jawab</th>
                        <th class="py-4 px-4">Jumlah Event</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4">Tanggal Daftar</th>
                        <th class="py-4 px-4 text-right">Tindakan Superadmin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium">
                    @forelse($organizers as $org)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $org->name }}</div>
                                <div class="text-xs text-slate-400">/organizer/{{ $org->slug }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">{{ $org->owner->name ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $org->owner->email ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 font-bold text-indigo-600">
                                {{ $org->events_count }} Event
                            </td>
                            <td class="py-4 px-4">
                                @if($org->status === 'approved')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">APPROVED</span>
                                @elseif($org->status === 'pending')
                                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">PENDING</span>
                                @elseif($org->status === 'rejected')
                                    <span class="px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-bold">REJECTED</span>
                                @else
                                    <span class="px-3 py-1 bg-slate-200 text-slate-700 rounded-full text-xs font-bold">SUSPENDED</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500">
                                {{ $org->created_at->format('d M Y') }}
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                @if($org->status !== 'approved')
                                    <form action="{{ route('admin.organizers.approve', $org->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition">
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                @if($org->status === 'pending')
                                    <form action="{{ route('admin.organizers.reject', $org->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition">
                                            Reject
                                        </button>
                                    </form>
                                @endif

                                @if($org->status === 'approved')
                                    <form action="{{ route('admin.organizers.suspend', $org->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-slate-700 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                                            Suspend
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Tidak ada data organisasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $organizers->links() }}
        </div>
    </div>
</div>
@endsection
