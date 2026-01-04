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

class TenantApplicationReceivedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenant;
    public $approveUrl;
    public $viewUrl;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->approveUrl = route('dashboard', [
            'tenant' => $tenant->id,
            'token' => $tenant->approval_token ?? $tenant->fresh()->generateApprovalToken()->approval_token
        ]);

        $this->viewUrl = route('dashboard', $tenant->id);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 New Tenant Application #' . $this->tenant->id . ' Received',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.tenant.admin-application-received',
        );
    }

    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->priority(1)
            ->with([
                'tenant' => $this->tenant,
                'approveUrl' => $this->approveUrl,
                'viewUrl' => $this->viewUrl,
                'companyName' => config('app.name', 'OC Workforce Housing'),
                'currentDate' => now()->format('F d, Y \a\t h:i A'),
            ]);
    }
}
