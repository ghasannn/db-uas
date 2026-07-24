<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventTicketMail;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('MIDTRANS CALLBACK', $request->all());

        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Order ID tidak ditemukan'], 400);
        }

        // Verifikasi Signature Midtrans
        $serverKey = config('midtrans.server_key');
        $mySignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $mySignature) {
            Log::warning('Signature Midtrans tidak valid', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        $transaction = Transaction::with('event')->where('order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaksi tidak ditemukan', ['order_id' => $orderId]);
            return response()->json(['message' => 'Transaction Not Found'], 404);
        }

        if (in_array($transaction->status, ['paid', 'success', 'settlement', 'free_claimed'])) {
            return response()->json(['message' => 'Already Processed']);
        }

        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus === 'challenge') {
                    $transaction->status = 'challenge';
                    $transaction->save();
                } else {
                    $this->processSuccess($transaction);
                }
                break;

            case 'settlement':
                $this->processSuccess($transaction);
                break;

            case 'pending':
                $transaction->status = 'pending';
                $transaction->save();
                break;

            case 'expire':
                $this->processExpiredOrFailed($transaction, 'expired');
                break;

            case 'cancel':
            case 'deny':
                $this->processExpiredOrFailed($transaction, 'failed');
                break;
        }

        Log::info('STATUS BERHASIL DIUPDATE', [
            'order_id' => $orderId,
            'status' => $transaction->status
        ]);

        return response()->json(['message' => 'OK']);
    }

    private function processSuccess(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $oldStatus = $transaction->status;
            $transaction->status = 'paid';
            $transaction->save();

            $event = Event::where('id', $transaction->event_id)->lockForUpdate()->first();
            if ($event) {
                if (strtolower($oldStatus) === 'reserved' || $transaction->reserved_at) {
                    if ($event->reserved_count > 0) {
                        $event->decrement('reserved_count');
                    }
                    // Stock was already decremented during reservation time!
                } else {
                    if ($event->stock > 0) {
                        $event->decrement('stock');
                    }
                }
                $event->increment('sold_count');
            }
        });

        // Email E-Ticket
        try {
            Mail::to($transaction->customer_email)->send(new EventTicketMail($transaction));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email E-Ticket: ' . $e->getMessage());
        }
    }

    private function processExpiredOrFailed(Transaction $transaction, string $newStatus)
    {
        DB::transaction(function () use ($transaction, $newStatus) {
            $oldStatus = $transaction->status;
            $transaction->status = $newStatus;
            $transaction->save();

            // Release reserved stock back to event stock (+1)
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
    }
}