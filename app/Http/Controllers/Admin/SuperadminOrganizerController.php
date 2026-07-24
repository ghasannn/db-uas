<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class SuperadminOrganizerController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = Organization::with('owner')->withCount('events', 'transactions');

        if ($status) {
            $query->where('status', $status);
        }

        $organizers = $query->latest()->paginate(15);

        return view('admin.organizers.index', compact('organizers', 'status'));
    }

    public function approve(Organization $organization)
    {
        $organization->update(['status' => 'approved']);

        return back()->with('success', 'Organisasi "' . $organization->name . '" telah berhasil DISETUJUI!');
    }

    public function reject(Organization $organization)
    {
        $organization->update(['status' => 'rejected']);

        return back()->with('info', 'Organisasi "' . $organization->name . '" telah DITOLAK.');
    }

    public function suspend(Organization $organization)
    {
        $organization->update(['status' => 'suspended']);

        return back()->with('warning', 'Organisasi "' . $organization->name . '" telah DITANGGUHKAN (Suspended).');
    }
}
