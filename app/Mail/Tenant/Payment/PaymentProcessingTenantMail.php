<?php

namespace App\Mail\Tenant\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentProcessingTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        Log::info("PaymentProcessingTenantMail:", [$data]);
        $this->data = $data;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Payment Processing - ' . $this->data['invoice_number'])
            ->view('emails.tenant.payment.payment-processing-tenant')
            ->with('data', $this->data);
    }
}
