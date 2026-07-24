@extends('layouts.admin')
@section('title', 'Kelola Kategori - Admin')
@section('page_title', 'Manajemen Kategori')
@section('page_subtitle', 'Kelola klasifikasi tema kegiatan event')
@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm h-fit">
        <h3 class="font-black text-lg mb-4 text-slate-900">Tambah Kategori</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nama Kategori</label>
                <input type="text" name="name" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-indigo-500" placeholder="Contoh: Konser Musik" required>
            </div>
            <button type="submit" class="w-full px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition text-sm">
                Simpan Kategori
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="font-black text-lg text-slate-900">Daftar Kategori</h3>
            <form action="{{ route('admin.categories.index') }}" method="GET" class="flex gap-2 w-full sm:max-w-xs">
                <input type="text" name="search" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-indigo-500" placeholder="Cari kategori..." value="{{ $search ?? '' }}">
                <button type="submit" class="px-3 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                    <tr>
                        <th class="px-8 py-4 w-24">ID</th>
                        <th class="px-8 py-4">Nama Kategori</th>
                        <th class="px-8 py-4 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm text-slate-600">
                    @forelse($categories as $category)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-8 py-5 font-bold text-slate-400">#{{ $category->id }}</td>
                        <td class="px-8 py-5 font-bold text-slate-900">{{ $category->name }}</td>
                        <td class="px-8 py-5">
                            <div class="flex justify-center gap-2">
                                <button onclick="openEditModal('{{ $category->id }}', '{{ $category->name }}')" class="px-4 py-2 bg-amber-50 text-amber-600 font-bold rounded-xl text-xs hover:bg-amber-100 transition">
                                    Edit
                                </button>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
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
                        <td colspan="3" class="px-8 py-10 text-center text-slate-400 italic font-medium">
                            Belum ada data kategori resmi yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="editCategoryModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl max-w-md w-full border border-slate-100 p-8 shadow-2xl transform transition-all">
        <h3 class="font-black text-xl mb-4 text-slate-900">Ubah Nama Kategori</h3>
        <form id="editCategoryForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nama Kategori Baru</label>
                <input type="text" id="edit_category_name" name="name" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-indigo-500" required>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name) {
        const form = document.getElementById('editCategoryForm');
        form.action = `/admin/categories/${id}`;
        document.getElementById('edit_category_name').value = name;
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editCategoryModal').classList.add('hidden');
    }
</script>
@endsection