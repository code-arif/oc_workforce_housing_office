<?php

namespace App\Mail\TenantApplication;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenantWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $dashboardUrl;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->dashboardUrl = route('dashboard', [
            'tenant' => $tenant->id,
            'token' => $tenant->approval_token
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome! Your Application Has Been Received',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.tenant.welcome',
        );
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.admin_email'), 'Application Support')
            ->with([
                'tenant' => $this->tenant,
                'dashboardUrl' => $this->dashboardUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', '(443) 336-5182'),
            ]);
    }
}
