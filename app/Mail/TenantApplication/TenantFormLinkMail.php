<?php

namespace App\Mail\TenantApplication;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantFormLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $formUrl;


    /**
     * Create a new message instance.
     */
    public function __construct($tenant, $formUrl)
    {
        $this->tenant = $tenant;
        $this->formUrl = $formUrl;

        Log::info('TenantFormLinkMail initialized with tenant email: ' . $tenant->email . ' and form URL: ' . $formUrl);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tenant Application Form Link Mail',
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
            ->with([
                'tenant' => $this->tenant,
                'formUrl' => $this->formUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'currentDate' => now()->format('F d, Y \a\t h:i A'),
                'applicationId' => $this->tenant->id,
                'tenantEmail' => $this->tenant->email,
                'tenantStatus' => ucfirst($this->tenant->status),
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
