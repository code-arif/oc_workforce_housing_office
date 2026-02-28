<?php

namespace App\Mail\Tenant\PasswordReset;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantPasswordResetOTPMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tenant;
    public $companyName;
    public $companyEmail;
    public $companyPhone;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->companyName = config('app.name');
        $this->companyEmail = config('mail.admin_email');
        $this->companyPhone = config('app.phone', '');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.password-reset-otp',
            with: [
                'tenant' => $this->tenant,
                'otp' => $this->tenant->otp,
                'otpExpiresIn' => 10, // minutes
                'companyName' => $this->companyName,
                'companyEmail' => $this->companyEmail,
                'companyPhone' => $this->companyPhone,
                'supportEmail' => $this->companyEmail,
                'currentYear' => date('Y'),
            ]
        );
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

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->replyTo($this->companyEmail, 'Tenant Support');
    }
}
