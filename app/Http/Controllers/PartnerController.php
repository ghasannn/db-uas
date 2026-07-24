<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    // Menampilkan daftar partner dan fitur Search Basic
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            $partners = Partner::where('name', 'LIKE', '%' . $search . '%')->get();
        } else {
            $partners = Partner::all();
        }

        return view('admin.partners.index', compact('partners', 'search'));
    }

    // membuat partner baru
    public function create()
    {
        return view('admin.partners.create');
    }

    // Menyimpan data partner dan upload logo
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $logoUrl = $request->file('logo')->store('partners', 'public');
        }

        Partner::create([
            'name' => $request->name,
            'logo_url' => $logoUrl,
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil ditambahkan!');
    }

    // mengedit partner
    public function edit($id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.edit', compact('partner'));
    }

    // Mengupdate data partner dan mengganti logo lama
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $partner = Partner::findOrFail($id);
        $logoUrl = $partner->logo_url;

        if ($request->hasFile('logo')) {
            if ($partner->logo_url) {
                Storage::disk('public')->delete($partner->logo_url);
            }
            $logoUrl = $request->file('logo')->store('partners', 'public');
        }

        $partner->update([
            'name' => $request->name,
            'logo_url' => $logoUrl,
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil diperbarui!');
    }

    // Menghapus data partner beserta logonya
    public function destroy($id)
    {
        $partner = Partner::findOrFail($id);

        if ($partner->logo_url) {
            Storage::disk('public')->delete($partner->logo_url);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'Partner berhasil dihapus!');
    }
}