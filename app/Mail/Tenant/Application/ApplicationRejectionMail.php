<?php

namespace App\Mail\Tenant\Application;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tenant;
    public $contactUrl;


    /**
     * Create a new message instance.
     */
    public function __construct($tenant, $contactUrl)
    {
        $this->tenant = $tenant;
        $this->contactUrl = $contactUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Application has been Rejected',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.application.application-rejected',
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
                'contactUrl' => $this->contactUrl,
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
