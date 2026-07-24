<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('event')->latest()->paginate(10);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function confirmPayment($id)
    {
        // Menggunakan Database Transaction untuk mencegah Race Condition penumpukan stok
        DB::transaction(function () use ($id) {
            $transaction = Transaction::findOrFail($id);
            
            if ($transaction->status === 'Pending') {
                $event = Event::findOrFail($transaction->event_id);
                
                // Cek ketersediaan stok sebelum dikonfirmasi
                if ($event->stock >= 1) {
                    // 1. Kurangi stok event secara permanen
                    $event->decrement('stock', 1);
                    
                    // 2. Ubah status transaksi menjadi Success
                    $transaction->update(['status' => 'Success']);
                } else {
                    throw new \Exception("Gagal konfirmasi, stok tiket untuk event ini telah habis.");
                }
            }
        });

        return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi dan tiket resmi telah diterbitkan!');
    }
}