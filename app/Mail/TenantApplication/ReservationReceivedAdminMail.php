<?php

namespace App\Mail\TenantApplication;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationReceivedAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $viewUrl;
    public $approveUrl;
    public $rejectUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;

        // Admin dashboard URL
        $this->viewUrl = config('app.url') . '/admin/applications';

        // Action URLs (optional - can be handled in frontend)
        $this->approveUrl = config('app.url') . '/admin/applications/' . $application->id . '/approve';
        $this->rejectUrl = config('app.url') . '/admin/applications/' . $application->id . '/reject';

        Log::info("ApplicationReceivedAdminMail initialized for Application ID: {$application->id}");
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->application->isCorporate()
            ? 'New Corporate Reservation Request #' . $this->application->id
            : 'New Individual Reservation Request #' . $this->application->id;

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
            view: 'emails.tenant.application.application-received-admin',
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
                'application' => $this->application,
                'viewUrl' => $this->viewUrl,
                'approveUrl' => $this->approveUrl,
                'rejectUrl' => $this->rejectUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'currentDate' => now()->format('F d, Y \a\t h:i A'),
                'applicationId' => $this->application->id,
                'applicationType' => ucfirst($this->application->type),
                'applicantName' => $this->application->full_name,
                'applicantEmail' => $this->application->email,
                'applicantPhone' => $this->application->phone,
                'companyName' => $this->application->company_name,
                'applicationStatus' => ucfirst($this->application->status),
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
