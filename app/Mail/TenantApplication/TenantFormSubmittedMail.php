<?php

namespace App\Mail\TenantApplication;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantFormSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $viewUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->viewUrl = config('app.url');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Tenant Application #' . $this->tenant->id . ' Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.tenant-form-submitted',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->priority(1) // High priority
            ->with([
                'tenant' => $this->tenant,
                'viewUrl' => $this->viewUrl,
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
