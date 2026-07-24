<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\OrganizerRegistrationController;
use App\Http\Controllers\OrganizerProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\GoogleController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Controller Admin & Organizer Area
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as EventAdminController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\SuperadminOrganizerController;
use App\Http\Controllers\Organizer\DashboardController as OrganizerDashboardController;

// ==========================================
// RUTE USER & PUBLIK
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/organizers', [OrganizerProfileController::class, 'index'])->name('organizer.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// PENDAFTARAN PENYELENGGARA / ORGANIZER (Fitur 4.3)
Route::get('/organizer/register', [OrganizerRegistrationController::class, 'showRegisterForm'])->name('organizer.register');
Route::post('/organizer/register', [OrganizerRegistrationController::class, 'register'])->name('organizer.register.post');

// DETAIL PENYELENGGARA / ORGANIZER (Parameter slug)
Route::get('/organizer/{slug}', [OrganizerProfileController::class, 'show'])->name('organizer.show');

// SSO GOOGLE ROUTING (Fitur 4.1)
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// RUTE CHECKOUT (Support Reserved Ticket & Free Event Bypass)
Route::get('/checkout/{event}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout/{event}', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/payment/{order_id}', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/checkout/cancel/{order_id}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
Route::get('/checkout/success/{order_id}', [CheckoutController::class, 'success'])->name('checkout.success');

// RUTE E-TICKET & RIWAYAT PESANAN SAYA
Route::get('/ticket/{order_id}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/my-ticket', function () {
    $transactions = [];
    if (auth('web')->check()) {
        $userEmail = auth('web')->user()->email;
        $userId = auth('web')->id();
        $transactions = \App\Models\Transaction::where(function ($q) use ($userId, $userEmail) {
                $q->where('user_id', $userId)
                  ->orWhere('customer_email', $userEmail);
            })
            ->with(['event', 'organization'])
            ->latest()
            ->get();

        foreach ($transactions as $trx) {
            if (!$trx->isPaid()) {
                $trx->syncMidtransStatus();
            }
        }
    }
    return view('my-orders', compact('transactions'));
})->middleware('auth:web')->name('ticket');

// RATING & REVIEW ENDPOINT (Fitur 4.2)
Route::post('/events/{event}/reviews', [ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('events.reviews.store');

// MIDTRANS WEBHOOK CALLBACK
Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])
    ->name('midtrans.callback');

// ==========================================
// RUTE AUTH & LOGIN
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// RUTE ORGANIZER / TENANT AREA (/organizer)
// ==========================================
Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'organizer'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});

// ==========================================
// RUTE ADMIN / SUPERADMIN AREA (/admin)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Login alias untuk admin
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Superadmin & Organizer Protected Routes
    Route::middleware(['auth:admin', 'organizer'])->group(function () {
        
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Management Event (Tenant Scoped via EventPolicy & TenantScope)
        Route::resource('events', EventAdminController::class);

        // Management Transaksi
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('transactions/{id}/confirm', [TransactionController::class, 'confirmPayment'])->name('transactions.confirm');

        // Management Partner & Kategori
        Route::resource('partners', PartnerController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'edit']);

        // Fitur khusus Superadmin
        Route::middleware('superadmin')->group(function () {
            Route::get('organizers', [SuperadminOrganizerController::class, 'index'])->name('organizers.index');
            Route::post('organizers/{organization}/approve', [SuperadminOrganizerController::class, 'approve'])->name('organizers.approve');
            Route::post('organizers/{organization}/reject', [SuperadminOrganizerController::class, 'reject'])->name('organizers.reject');
            Route::post('organizers/{organization}/suspend', [SuperadminOrganizerController::class, 'suspend'])->name('organizers.suspend');
        });
    });
});

// QR Code Test
Route::get('/test-qr', function () {
    return QrCode::size(300)->margin(2)->generate('Selamat Datang di AmikomEventHub!');
});