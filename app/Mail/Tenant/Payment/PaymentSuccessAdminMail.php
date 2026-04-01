<?php

namespace App\Mail\Tenant\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Payment Received - ' . $this->data['invoice_number'])
            ->view('emails.tenant.payment.payment-success-admin')
            ->with('data', $this->data);
    }
}
