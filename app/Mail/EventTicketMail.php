<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from('hello@amikomeventhub.com', 'Admin Event Amikom')
            ->subject('E-Ticket Resmi Anda - ' . $this->transaction->order_id)
            ->view('emails.ticket'); // Mengarah ke template email di views
    }
}
