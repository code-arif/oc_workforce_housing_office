<?php

namespace App\Mail\Tenant;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeaseCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lease;
    public $tenant;
    public $property;
    public $bed;

    /**
     * Create a new message instance.
     */
    public function __construct(Lease $lease)
    {
        $this->lease = $lease;
        $this->tenant = $lease->tenant;
        $this->property = $lease->property;
        $this->bed = $lease->assignments()->first()->bed ?? null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Lease Has Ended - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.lease-completed',
            with: [
                'lease' => $this->lease,
                'tenant' => $this->tenant,
                'property' => $this->property,
                'bed' => $this->bed,
                'companyName' => config('app.name'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', ''),
                'subject' => 'Your Lease Has Ended',
            ]
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->replyTo(config('mail.admin_email'), 'Tenant Support');
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
