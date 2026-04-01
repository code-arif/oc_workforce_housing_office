<?php

namespace App\Mail\Application;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationSubmittedConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $supportUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->supportUrl = config('app.frontend_url') . '/contact';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reservation Request Received - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.application.application-submitted-confirmation',
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Support Team')
            ->with([
                'application' => $this->application,
                'supportUrl' => $this->supportUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
                'applicantName' => $this->application->full_name,
                'applicationType' => ucfirst($this->application->type),
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
