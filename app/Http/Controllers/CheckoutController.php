<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EventTicketMail;

class CheckoutController extends Controller
{
    public function create(Event $event)
    {
        $categories = Category::all();
        return view('checkout.create', compact('event', 'categories'));
    }

    public function store(Request $request, Event $event)
    {
        // 1. Validasi Input Kredensial Pelanggan
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $orderId = 'TRX-' . time() . '-' . Str::random(5);

        // 2. LOGIKA PERCABANGAN: FITUR 5.2 — EVENT GRATIS (BYPASS MIDTRANS)
        if ($event->price == 0 || $event->is_free) {
            try {
                $transaction = DB::transaction(function () use ($event, $request, $orderId, $user) {
                    // Pessimistic Locking untuk mencegah race condition meski tiket gratis
                    $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->first();

                    if ($lockedEvent->availableStock() <= 0) {
                        throw new \Exception('Mohon maaf, kuota tiket gratis untuk acara ini sudah habis.');
                    }

                    // Direct increment sold_count for free claim
                    $lockedEvent->increment('sold_count');
                    if ($lockedEvent->stock > 0) {
                        $lockedEvent->decrement('stock');
                    }

                    return Transaction::create([
                        'organization_id' => $lockedEvent->organization_id,
                        'user_id'         => $user ? $user->id : null,
                        'event_id'        => $lockedEvent->id,
                        'order_id'        => $orderId,
                        'customer_name'   => $request->customer_name,
                        'customer_email'  => $request->customer_email,
                        'customer_phone'  => $request->customer_phone,
                        'total_price'     => 0,
                        'status'          => 'free_claimed',
                    ]);
                });

                // Kirim Email E-Ticket secara async/direct
                try {
                    Mail::to($transaction->customer_email)->send(new EventTicketMail($transaction));
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email E-Ticket gratis: ' . $e->getMessage());
                }

                // Redirect langsung ke halaman sukses E-Ticket (Bypass Midtrans)
                return redirect()->route('checkout.success', $transaction->order_id)
                    ->with('success', 'Selamat! Tiket gratis Anda berhasil diklaim.');
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // 3. LOGIKA FITUR 5.1 — RESERVED TICKET (PREVENT RACE CONDITION) UNTUK EVENT BERBAYAR
        try {
            $adminFee = ($event->price == 0 || $event->is_free) ? 0 : 5000;
            $totalPrice = $event->price + $adminFee;

            $transaction = DB::transaction(function () use ($event, $request, $orderId, $totalPrice, $user) {
                // Pessimistic Locking untuk menahan stok
                $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->first();

                if ($lockedEvent->availableStock() <= 0) {
                    throw new \Exception('Mohon maaf, tiket untuk acara ini sudah habis atau sedang direbut pembeli lain.');
                }

                // Increment reserved_count (Tahan stok) & decrement stock immediately by 1
                $lockedEvent->increment('reserved_count');
                if ($lockedEvent->stock > 0) {
                    $lockedEvent->decrement('stock');
                }

                return Transaction::create([
                    'organization_id' => $lockedEvent->organization_id,
                    'user_id'         => $user ? $user->id : null,
                    'event_id'        => $lockedEvent->id,
                    'order_id'        => $orderId,
                    'customer_name'   => $request->customer_name,
                    'customer_email'  => $request->customer_email,
                    'customer_phone'  => $request->customer_phone,
                    'total_price'     => $totalPrice,
                    'status'          => 'reserved', // Status Awal Tahan Stok
                    'reserved_at'     => now(),
                    'expires_at'      => now()->addMinutes(15), // Tiket ditahan 15 menit
                ]);
            });

            // INTEGRASI MIDTRANS (SNAP TOKEN)
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->order_id,
                    'gross_amount' => $transaction->total_price,
                ],
                'customer_details' => [
                    'first_name' => $transaction->customer_name,
                    'email' => $transaction->customer_email,
                    'phone' => $transaction->customer_phone,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $transaction->update(['snap_token' => $snapToken]);

            return redirect()->route('checkout.payment', $transaction->order_id);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function payment($order_id)
    {
        $categories = Category::all();
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        // Sync status with Midtrans API in case payment completed on simulator/banking app
        $transaction->syncMidtransStatus();

        if ($transaction->isPaid()) {
            return redirect()->route('checkout.success', $transaction->order_id)
                ->with('success', 'Pembayaran Anda telah berhasil dikonfirmasi!');
        }

        return view('checkout.payment', compact('transaction', 'categories'));
    }

    public function success($order_id)
    {
        $categories = Category::all();
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        // Sync status with Midtrans API
        $transaction->syncMidtransStatus();

        if (!$transaction->isPaid()) {
            return redirect()->route('checkout.payment', $transaction->order_id)
                ->with('warning', 'Pembayaran belum terkonfirmasi oleh sistem/Midtrans. Silakan selesaikan pembayaran terlebih dahulu.');
        }

        return view('checkout.success', compact('transaction', 'categories'));
    }

    public function cancel($order_id)
    {
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        if ($transaction->isPaid()) {
            return back()->with('error', 'Pesanan yang sudah lunas tidak dapat dibatalkan.');
        }

        if (in_array(strtolower($transaction->status), ['cancelled', 'expired', 'failed'])) {
            return redirect()->route('events.show', $transaction->event_id)
                ->with('info', 'Pemesanan ini telah dibatalkan atau kadaluarsa sebelumnya.');
        }

        // Try canceling transaction on Midtrans API if available
        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            \Midtrans\Transaction::cancel($transaction->order_id);
        } catch (\Exception $e) {
            Log::info('Midtrans cancel notice for order ' . $transaction->order_id . ': ' . $e->getMessage());
        }

        // Cancel order and return reserved stock
        DB::transaction(function () use ($transaction) {
            $oldStatus = $transaction->status;
            $transaction->update(['status' => 'cancelled']);

            if (strtolower($oldStatus) === 'reserved' || $transaction->reserved_at) {
                $event = Event::where('id', $transaction->event_id)->lockForUpdate()->first();
                if ($event) {
                    if ($event->reserved_count > 0) {
                        $event->decrement('reserved_count');
                    }
                    $event->increment('stock'); // Return stock +1 back to event
                }
            }
        });

        return redirect()->route('events.show', $transaction->event_id)
            ->with('success', 'Pemesanan tiket berhasil dibatalkan. Stok tiket telah dikembalikan.')
            ->with('order_cancelled', 'Pesanan dengan kode #' . $transaction->order_id . ' telah berhasil dibatalkan. Stok tiket telah dikembalikan ke ketersediaan event.');
    }
}