<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $query = Event::with(['category', 'organization'])->latest();

        // Scope to tenant if organizer
        if ($user->isOrganizer() && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        $events = $query->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        $categories = Category::all();
        
        $isApproved = true;
        if ($user && $user->isOrganizer()) {
            if (!$user->organization || $user->organization->status !== 'approved') {
                $isApproved = false;
            }
        }

        return view('admin.events.create', compact('categories', 'isApproved'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if ($user->isOrganizer()) {
            if (!$user->organization || $user->organization->status !== 'approved') {
                return back()->withInput()->with('error', 'Akun anda belum di approve oleh super admin');
            }
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'poster' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        if ($user->isSuperadmin()) {
            $adminOrg = \App\Models\Organization::firstOrCreate(
                ['slug' => 'admin-amikom'],
                [
                    'name' => 'Admin Amikom',
                    'owner_user_id' => $user->id,
                    'description' => 'Penyelenggara Event Resmi Admin Amikom',
                    'status' => 'approved',
                ]
            );

            if (!$user->organization_id) {
                $user->update(['organization_id' => $adminOrg->id]);
            }

            $data['organization_id'] = $user->organization_id ?? $adminOrg->id;
        } else {
            $data['organization_id'] = $user->organization_id;
        }
        $data['is_free'] = ((float)$data['price'] === 0.0);
        $data['quota'] = (int)$data['stock'];
        $data['status'] = 'published';

        Event::create($data);

        return redirect()->route('admin.events.index')->with('success', 'Data Event berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $categories = Category::all();
        return view('event-detail', compact('categories', 'event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $user = Auth::user();

        // Tenant authorization policy check (403 if trying to edit another tenant's event)
        if (!$user->isSuperadmin()) {
            if ($event->organization_id && (int)$user->organization_id !== (int)$event->organization_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah event milik organisasi lain.');
            }
        }

        $categories = Category::all();
        return view('admin.events.edit', compact('event', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user->isSuperadmin()) {
            if ($event->organization_id && (int)$user->organization_id !== (int)$event->organization_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengubah event milik organisasi lain.');
            }
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'poster' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) {
                Storage::disk('public')->delete($event->poster_path);
            }
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        $data['is_free'] = ((float)$data['price'] === 0.0);
        $data['quota'] = (int)$data['stock'];

        $event->update($data);

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $user = Auth::user();

        if (!$user->isSuperadmin()) {
            if ($event->organization_id && (int)$user->organization_id !== (int)$event->organization_id) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk menghapus event milik organisasi lain.');
            }
        }

        if ($event->poster_path) {
            Storage::disk('public')->delete($event->poster_path);
        }

        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Data event berhasil dihapus secara permanen.');
    }
}