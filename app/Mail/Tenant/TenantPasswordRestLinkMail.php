<?php

namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantPasswordRestLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $passResetUrl;


    /**
     * Create a new message instance.
     */
    public function __construct($tenant, $passResetUrl)
    {
        $this->tenant = $tenant;
        $this->passResetUrl = $passResetUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tenant Form Link Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.tenant-password-reset-link',
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
                'passResetUrl' => $this->passResetUrl,
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
