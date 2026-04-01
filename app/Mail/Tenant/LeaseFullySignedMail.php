<?php

namespace App\Mail\Tenant;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeaseFullySignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $lease;
    public $tenant;
    public $property;
    public $bed;

    public function __construct(Lease $lease)
    {
        $this->lease    = $lease;
        $this->tenant   = $lease->tenant;
        $this->property = $lease->property;
        $this->bed      = $lease->assignments()->where('is_current', true)->first()?->bed ?? null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Lease is Fully Signed — Welcome! - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.lease-fully-signed',
        );
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Tenant Support')
            ->with([
                'tenant'        => $this->tenant,
                'property'      => $this->property,
                'bed'           => $this->bed,
                'lease'         => $this->lease,
                'dashboardUrl'  => config('app.frontend_url', 'http://localhost:3000') . '/dashboard',
                'paymentsUrl'   => config('app.frontend_url', 'http://localhost:3000') . '/dashboard/invoices',
                'companyName'   => config('app.name', 'OC Workforce Housing'),
                'companyEmail'  => config('mail.admin_email'),
                'companyPhone'  => config('app.phone', '(443) 336-5182'),
                'currentYear'   => now()->year,
            ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
