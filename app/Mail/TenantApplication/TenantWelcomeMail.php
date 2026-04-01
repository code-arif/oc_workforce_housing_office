<?php

namespace App\Mail\TenantApplication;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lease;
    public $tenant;
    public $property;
    public $supportUrl;
    public $tenantPortalUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Lease $lease)
    {
        $this->lease = $lease;
        $this->tenant = $lease->tenant;
        $this->property = $lease->property;

        // Support URL or general info page
        $this->supportUrl = config('app.frontend_url') . '/contact';
        $this->tenantPortalUrl = config('app.frontend_url') . '/dashboard';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Your Tenancy Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.welcome',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Tenant Support')
            ->with([
                'tenant' => $this->tenant,
                'property' => $this->property,
                'lease' => $this->lease,
                'supportUrl' => $this->supportUrl,
                'tenantPortalUrl' => $this->tenantPortalUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'senderName' => 'Property Management Team',
                'currentYear' => now()->year,
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