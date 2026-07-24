<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk memberikan ulasan.');
        }

        // 1. Verify event has ended
        if ($event->date > now()) {
            return back()->with('error', 'Anda hanya dapat memberikan ulasan setelah event selesai diselenggarakan.');
        }

        // 2. Verify user has a valid paid/free_claimed ticket for this event
        $hasValidTicket = Transaction::where('event_id', $event->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('customer_email', $user->email);
            })
            ->whereIn('status', ['paid', 'success', 'settlement', 'free_claimed'])
            ->exists();

        if (!$hasValidTicket) {
            return back()->with('error', 'Hanya pembeli tiket terverifikasi yang dapat memberikan ulasan.');
        }

        // 3. Check duplicate review
        $alreadyReviewed = Review::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk event ini sebelumnya.');
        }

        // 4. Validate rating & comment
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        Review::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'organization_id' => $event->organization_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Terima kasih! Ulasan & rating Anda berhasil dikirim.');
    }
}
