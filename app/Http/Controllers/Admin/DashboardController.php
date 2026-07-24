<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user();

        // 1. If buyer user tries to access dashboard, redirect to admin login
        if (!$user || $user->isBuyer()) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Silakan login sebagai Admin atau Organizer untuk mengakses halaman dashboard.');
        }

        // 2. Metrics for Organizer
        if ($user->isOrganizer() && !$user->isSuperadmin()) {
            $orgId = $user->organization_id;

            $totalRevenue = Transaction::where('organization_id', $orgId)
                ->whereIn('status', ['paid', 'success', 'settlement'])
                ->sum('total_price');

            $ticketsSold = Transaction::where('organization_id', $orgId)
                ->whereIn('status', ['paid', 'success', 'settlement', 'free_claimed'])
                ->count();

            $activeEvents = Event::where('organization_id', $orgId)
                ->where('date', '>=', now())
                ->count();

            $pendingOrders = Transaction::where('organization_id', $orgId)
                ->where(function ($q) {
                    $q->where('status', 'pending')
                      ->orWhere('status', 'reserved');
                })
                ->count();

            $recentTransactions = Transaction::where('organization_id', $orgId)
                ->with(['event', 'organization'])
                ->latest()
                ->take(5)
                ->get();

            return view('admin.dashboard', compact(
                'totalRevenue',
                'ticketsSold',
                'activeEvents',
                'pendingOrders',
                'recentTransactions'
            ));
        }

        // 3. Platform-wide metrics for Superadmin
        $totalRevenue = Transaction::whereIn('status', ['paid', 'success', 'settlement'])->sum('total_price');
        $ticketsSold = Transaction::whereIn('status', ['paid', 'success', 'settlement', 'free_claimed'])->count();
        $activeEvents = Event::where('date', '>=', now())->count();
        $totalOrganizations = Organization::count();
        $pendingOrganizations = Organization::where('status', 'pending')->count();
        $pendingOrders = Transaction::whereIn('status', ['pending', 'reserved'])->count();
        $totalCategories = \App\Models\Category::count();
        $totalPartners = \App\Models\Partner::count();

        $recentTransactions = Transaction::with(['event', 'organization'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'ticketsSold',
            'activeEvents',
            'totalOrganizations',
            'pendingOrganizations',
            'pendingOrders',
            'totalCategories',
            'totalPartners',
            'recentTransactions'
        ));
    }
}