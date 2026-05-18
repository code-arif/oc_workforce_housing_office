<?php

namespace App\Mail\Tenant;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class NewLeaseDetailsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lease;
    public $passResetUrl;
    public $sendWelcome;
    public $sendSignature;
    public $leaseDocument;
    public $tenant;
    public $property;
    public $bed;
    public $signatureUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Lease $lease, $passResetUrl = null, $sendWelcome = false, $sendSignature = false, $leaseDocument = null)
    {
        $this->lease = $lease;
        $this->passResetUrl = $passResetUrl;
        $this->sendWelcome = $sendWelcome;
        $this->sendSignature = $sendSignature;
        $this->leaseDocument = $leaseDocument;

        $this->tenant = $lease->tenant;
        $this->property = $lease->property;
        $this->bed = $lease->assignments()->first()->bed ?? null;

        // Build the API URL for the tenant dashboard (Next.js frontend)
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $this->signatureUrl = $frontendUrl . '/dashboard/';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Action Required: Your Lease & Account Details - ' . config('app.name');
        if (!$this->sendSignature && !$this->passResetUrl) {
            $subject = 'Welcome to ' . config('app.name') . '!';
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.new-lease-details',
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
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email', 'support@ocworkforcehousing.com'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'tenantPortalUrl' => config('app.frontend_url', 'http://localhost:3000') . '/login',
                'supportUrl' => config('app.frontend_url', 'http://localhost:3000') . '/contact',
                'passResetUrl' => $this->passResetUrl,
                'sendWelcome' => $this->sendWelcome,
                'sendSignature' => $this->sendSignature,
                'signatureUrl' => $this->signatureUrl,
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
