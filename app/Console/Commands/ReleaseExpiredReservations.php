<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expired-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired ticket reservations and return reserved stock to quota';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredTransactions = Transaction::where('status', 'reserved')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredTransactions as $transaction) {
            DB::transaction(function () use ($transaction, &$count) {
                $transaction->update(['status' => 'expired']);

                $event = Event::where('id', $transaction->event_id)->lockForUpdate()->first();
                if ($event && $event->reserved_count > 0) {
                    $event->decrement('reserved_count');
                }

                $count++;
            });
        }

        $this->info("Berhasil merilis {$count} reservasi tiket yang expired.");
        Log::info("ReleaseExpiredReservations executed: {$count} reservations released.");

        return Command::SUCCESS;
    }
}
