<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\Partner; // Ambil Model Partner
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua jenis kategori untuk tampilan filter tab button
        $categories = Category::all();
        
        // semua data partner untuk halaman
        $partners = Partner::all(); 

        $query = Event::with('category')
            ->where('date', '>=', now())
            ->orderBy('date', 'asc');
        if ($request->has('category') && $request->category != '') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        $events = $query->get();
        return view('welcome', compact('events', 'categories', 'partners'));
    }
}