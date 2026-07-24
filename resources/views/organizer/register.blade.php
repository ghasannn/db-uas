@extends('layouts.app')

@section('content')
<main class="max-w-xl mx-auto px-6 py-12">
    <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-100 shadow-xl">
        <div class="text-center mb-8">
            <span class="px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold uppercase tracking-wider">
                Pendaftaran Pengelola Event
            </span>
            <h1 class="text-3xl font-black text-slate-900 mt-3">
                Daftar Pengelola Event
            </h1>
            <p class="text-slate-500 text-sm mt-2">
                Masukkan Nama Organisasi, Email, dan Password yang akan digunakan untuk login ke Dashboard Admin.
            </p>
        </div>

        @if(session('info'))
            <div class="bg-blue-100 text-blue-800 p-4 rounded-2xl mb-6 font-medium text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl mb-6 text-sm">
                <p class="font-bold mb-1">Mohon perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('organizer.register.post') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Nama Organisasi -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Organisasi / Kepanitiaan <span class="text-rose-500">*</span></label>
                <input type="text" name="organization_name" placeholder="Contoh: BEM Universitas Amikom, HIMIKA" value="{{ old('organization_name') }}" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition font-medium" required>
            </div>

            <!-- Email Login Admin -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email (Login Admin) <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}" placeholder="email.admin@domain.com" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition font-medium" required>
            </div>

            <!-- Password Admin -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition font-medium" placeholder="Masukkan password (min 6 karakter)" {{ auth()->check() ? '' : 'required' }}>
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Konfirmasi Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition font-medium" placeholder="Ulangi password" {{ auth()->check() ? '' : 'required' }}>
            </div>

            <!-- Info Catatan ACC Superadmin -->
            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start gap-2.5">
                <span class="text-lg leading-none">⚠️</span>
                <div>
                    <strong>Persetujuan (ACC) Superadmin:</strong> Setelah submit, pendaftaran harus di-<strong>ACC oleh Superadmin</strong> sebelum Anda dapat mengelola event di Dashboard Admin.
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-base shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition">
                Daftar Pengelola Event &rarr;
            </button>
        </form>
    </div>
</main>
@endsection
