<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Category;

class OrganizerProfileController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $organizers = Organization::where('status', 'approved')
            ->withCount(['events', 'reviews'])
            ->with(['events' => function ($q) {
                $q->where('status', 'published')->latest()->take(3);
            }])
            ->latest()
            ->paginate(9);

        return view('organizer.index', compact('organizers', 'categories'));
    }

    public function show($slug)
    {
        $organization = Organization::where('slug', $slug)
            ->withCount(['events', 'reviews'])
            ->firstOrFail();

        $categories = Category::all();

        $events = $organization->events()
            ->where('status', 'published')
            ->latest()
            ->paginate(9);

        $reviews = $organization->reviews()
            ->with(['user', 'event'])
            ->latest()
            ->paginate(10);

        $averageRating = $organization->averageRating();

        return view('organizer.show', compact('organization', 'categories', 'events', 'reviews', 'averageRating'));
    }
}
