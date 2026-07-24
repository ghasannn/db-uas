<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;     // PENTING: Untuk membaca data Event
use App\Models\Category;  // PENTING: Untuk menu navigasi/footer
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        //
    }

    public function show(Event $event)
    {
        $categories = Category::all();
        $event->load(['reviews.user', 'organization', 'category']);
        return view('event-detail', compact('categories', 'event'));
    }

    public function checkout()
    {
        return view('checkout');
    }

    public function ticket()
    {
        return view('ticket');
    }
}