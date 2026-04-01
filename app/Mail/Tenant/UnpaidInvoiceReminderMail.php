<?php

namespace App\Mail\Tenant;

use App\Models\Tenant;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class UnpaidInvoiceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $tenant;
    public $lease;
    public $property;
    public $daysOverdue;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->invoice = $invoice;
        $this->lease = $invoice->lease;
        $this->property = $invoice->lease?->property;
        $this->daysOverdue = now()->diffInDays($invoice->due_date);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder: Invoice #' . ($this->invoice->invoice_number ?? 'INV-' . str_pad($this->invoice->id, 5, '0', STR_PAD_LEFT)) . ' is Overdue',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant.unpaid-invoice-reminder',
            with: [
                'invoice' => $this->invoice,
                'tenant' => $this->tenant,
                'lease' => $this->lease,
                'property' => $this->property,
                'daysOverdue' => $this->daysOverdue,
                'companyName' => config('app.name'),
                'companyEmail' => config('mail.admin_email'),
                'companyPhone' => config('app.phone', ''),
                'subject' => 'Payment Reminder - Invoice Overdue',
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
        try {
            // Generate the invoice PDF
            $invoice = $this->invoice;
            $lease = $this->lease;

            // Determine if this is the first invoice
            $firstInvoice = $lease?->invoices()
                ->where('type', 'RENT')
                ->orderBy('created_at', 'asc')
                ->first();

            $isFirstInvoice = $firstInvoice && $firstInvoice->id === $invoice->id;

            // Calculate totals
            $totalDue = $invoice->total_amount;
            
            if (!$totalDue || $totalDue == 0) {
                $totalDue = $invoice->amount;
                if ($isFirstInvoice && $lease && !$lease->deposit_collected && $lease->deposit_amount > 0) {
                    $totalDue += $lease->deposit_amount;
                }
            }

            $totalPaid = $invoice->paid_amount ?? $invoice->payments->sum('amount');
            $balanceDue = $invoice->balance_due ?? ($totalDue - $totalPaid);

            $data = compact(
                'invoice', 
                'lease', 
                'isFirstInvoice',
                'totalDue',
                'totalPaid',
                'balanceDue'
            );

            $pdf = Pdf::loadView('backend.layouts.leases.invoice.pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'Invoice-' . ($invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT)) . '.pdf';

            return [
                Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            // Log the error but don't fail the email
            \Illuminate\Support\Facades\Log::error('Failed to attach invoice PDF: ' . $e->getMessage());
            return [];
        }
    }
}
