<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'event_id',
        'order_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_price',
        'status',
        'snap_token',
        'reserved_at',
        'expires_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return in_array(strtolower($this->status), ['paid', 'success', 'settlement', 'free_claimed']);
    }

    public function syncMidtransStatus()
    {
        if ($this->isPaid() || in_array(strtolower($this->status), ['expired', 'failed', 'cancelled'])) {
            return $this;
        }

        // Check if reservation window has expired
        if (strtolower($this->status) === 'reserved' && $this->expires_at && $this->expires_at->isPast()) {
            \Illuminate\Support\Facades\DB::transaction(function () {
                $this->status = 'expired';
                $this->save();

                $event = Event::where('id', $this->event_id)->lockForUpdate()->first();
                if ($event) {
                    if ($event->reserved_count > 0) {
                        $event->decrement('reserved_count');
                    }
                    $event->increment('stock'); // Return stock +1 back to event
                }
            });
            return $this;
        }

        // Query Midtrans status API
        try {
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $status = \Midtrans\Transaction::status($this->order_id);

            if ($status) {
                $trx_status = is_array($status) ? ($status['transaction_status'] ?? '') : ($status->transaction_status ?? '');
                $fraud_status = is_array($status) ? ($status['fraud_status'] ?? '') : ($status->fraud_status ?? '');

                if (in_array(strtolower($trx_status), ['settlement', 'capture', 'success']) && strtolower($fraud_status) !== 'challenge') {
                    \Illuminate\Support\Facades\DB::transaction(function () {
                        $oldStatus = $this->status;
                        $this->status = 'paid';
                        $this->save();

                        $event = Event::where('id', $this->event_id)->lockForUpdate()->first();
                        if ($event) {
                            if (strtolower($oldStatus) === 'reserved' || $this->reserved_at) {
                                if ($event->reserved_count > 0) {
                                    $event->decrement('reserved_count');
                                }
                                // Note: stock was already decremented at reservation time!
                            } else {
                                if ($event->stock > 0) {
                                    $event->decrement('stock');
                                }
                            }
                            $event->increment('sold_count');
                        }
                    });

                    try {
                        \Illuminate\Support\Facades\Mail::to($this->customer_email)->send(new \App\Mail\EventTicketMail($this));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Gagal mengirim email E-Ticket: ' . $e->getMessage());
                    }
                } elseif (in_array(strtolower($trx_status), ['expire', 'cancel', 'deny'])) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($trx_status) {
                        $oldStatus = $this->status;
                        $this->status = (strtolower($trx_status) === 'expire') ? 'expired' : 'failed';
                        $this->save();

                        if (strtolower($oldStatus) === 'reserved' || $this->reserved_at) {
                            $event = Event::where('id', $this->event_id)->lockForUpdate()->first();
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal sync status Midtrans untuk order ' . $this->order_id . ': ' . $e->getMessage());
        }

        return $this->fresh();
    }
}
