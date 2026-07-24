<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organization = $user->organization;

        if (!$organization) {
            return redirect()->route('organizer.register')->with('info', 'Silakan daftarkan organisasi Anda terlebih dahulu.');
        }

        $events = Event::where('organization_id', $organization->id)->latest()->get();

        $totalRevenue = Transaction::where('organization_id', $organization->id)
            ->whereIn('status', ['paid', 'success', 'settlement'])
            ->sum('total_price');

        $ticketsSold = Event::where('organization_id', $organization->id)->sum('sold_count');

        $averageRating = $organization->averageRating();
        $totalReviews = $organization->reviews()->count();

        return view('organizer.dashboard', compact(
            'organization',
            'events',
            'totalRevenue',
            'ticketsSold',
            'averageRating',
            'totalReviews'
        ));
    }
}
