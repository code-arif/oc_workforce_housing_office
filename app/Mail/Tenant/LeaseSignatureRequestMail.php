<?php

namespace App\Mail\Tenant;

use App\Models\Lease;
use App\Models\Lease\LeaseDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeaseSignatureRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lease;
    public $tenant;
    public $property;
    public $leaseDocument;
    public $signatureUrl;
    public $bed;

    /**
     * Create a new message instance.
     */
    public function __construct(Lease $lease, LeaseDocument $leaseDocument)
    {
        $this->lease = $lease;
        $this->leaseDocument = $leaseDocument;
        $this->tenant = $lease->tenant;
        $this->property = $lease->property;
        $this->bed = $lease->assignments()->first()->bed ?? null;

        // Build the API URL for the tenant dashboard (Next.js frontend)
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $this->signatureUrl = $frontendUrl . '/tenant/lease/sign/' . $leaseDocument->id . '?token=' . $this->generateSignatureToken();
    }

    /**
     * Generate a secure token for the signature URL
     */
    protected function generateSignatureToken(): string
    {
        return base64_encode(json_encode([
            'document_id' => $this->leaseDocument->id,
            'tenant_id' => $this->tenant->id,
            'lease_id' => $this->lease->id,
            'expires' => now()->addDays(7)->timestamp,
        ]));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Sign Your Lease Agreement - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.lease-signature-request',
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
                'bed' => $this->bed,
                'lease' => $this->lease,
                'leaseDocument' => $this->leaseDocument,
                'signatureUrl' => $this->signatureUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'senderName' => 'Property Management Team',
                'currentYear' => now()->year,
                'expirationDays' => 7,
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
