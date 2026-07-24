<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function show($order_id)
    {
        // Mencari data transaksi berdasarkan Order ID beserta relasi event terkait
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        // Sync status dengan API Midtrans (jika user baru saja membayar di simulator / banking app)
        $transaction->syncMidtransStatus();

        // Validasi Keamanan: Tiket hanya diterbitkan jika status transaksi sudah Paid / Free Claimed
        if (!$transaction->isPaid()) {
            return redirect()->route('checkout.payment', $transaction->order_id)
                ->with('warning', 'Pembayaran untuk pesanan ini belum dikonfirmasi. Silakan selesaikan pembayaran.');
        }

        return view('ticket', compact('transaction'));
    }
}