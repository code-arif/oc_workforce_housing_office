<?php

namespace App\Mail\TenantApplication;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantFormSubmissionSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $supportUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome! Your Application Has Been Received - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.application-submission-success',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Application Support')
            ->with([
                'tenant' => $this->tenant,
                'supportUrl' => config('app.frontend_url') . '/contact',
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'tenantEmail' => $this->tenant->email,
                'applicationId' => $this->tenant->id,
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
