<?php

namespace App\Mail\TenantApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantFormLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $email;
    public $formUrl;


    /**
     * Create a new message instance.
     */
    public function __construct($email, $formUrl)
    {
        $this->email = $email;
        $this->formUrl = $formUrl;
        Log::info('TenantFormLinkMail initialized and form URL: ' . $formUrl);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Submit Your Tenant Application - OC Workforce Housing',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.tenant-form-link',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Support Team')
            ->with([
                'email' => $this->email,
                'formUrl' => $this->formUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'companyWebsite' => config('app.url'),
                'currentDate' => now()->format('F d, Y \a\t h:i A'),
                'applicationId' => null,
                'tenantEmail' => $this->email,
                'tenantStatus' => null,
            ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
