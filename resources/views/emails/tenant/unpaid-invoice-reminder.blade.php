@extends('emails.layout.mail')

@section('content')
    <div class="greeting">
        Payment Reminder - Invoice Overdue
    </div>

    <div class="message-content">
        <p>Dear {{ $tenant->profile->first_name ?? 'Tenant' }} {{ $tenant->profile->last_name ?? '' }},</p>

        <p>This is a friendly reminder that your invoice is <strong>{{ round($daysOverdue) }} day(s) overdue</strong>. Please arrange payment at your earliest convenience to avoid any late fees or service interruptions.</p>

        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">📄 Invoice Details:</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
            <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('F d, Y') }}</p>
            <p><strong>Due Date:</strong> <span style="color: #dc3545; font-weight: 600;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}</span></p>
            <p><strong>Total Amount:</strong> ${{ number_format($invoice->total_amount ?? $invoice->amount, 2) }}</p>
            @if($invoice->paid_amount > 0)
                <p><strong>Amount Paid:</strong> ${{ number_format($invoice->paid_amount, 2) }}</p>
            @endif
            <p><strong>Balance Due:</strong> <span style="color: #dc3545; font-weight: 700; font-size: 18px;">${{ number_format($invoice->balance_due ?? ($invoice->total_amount - $invoice->paid_amount), 2) }}</span></p>
        </div>

        @if($property)
        <div class="highlight-box">
            <h3 style="color: #ba9779; margin-bottom: 15px;">🏠 Property Information:</h3>
            <p><strong>Property:</strong> {{ $property->name ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $property->address ?? 'N/A' }}{{ isset($property->city) ? ', ' . $property->city : '' }}{{ isset($property->state) ? ', ' . $property->state : '' }} {{ $property->zip_code ?? '' }}</p>
        </div>
        @endif

        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin: 25px 0; border-radius: 0 4px 4px 0;">
            <h3 style="color: #721c24; margin-bottom: 10px;">⚠️ Important Notice:</h3>
            <p style="color: #721c24; margin: 0;">Your payment is <strong>{{ round($daysOverdue) }} day(s) overdue</strong>. To avoid additional late fees or potential service interruptions, please make your payment as soon as possible.</p>
        </div>

        <div class="highlight-box" style="border-left-color: #28a745; margin-top: 25px;">
            <h3 style="color: #155724; margin-bottom: 15px;">💳 Payment Options:</h3>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Log in to your tenant portal to make an online payment</li>
                <li>Contact our office for alternative payment arrangements</li>
                <li>Mail a check to our office address</li>
            </ul>
        </div>

        <p style="margin-top: 25px;">If you have already made this payment, please disregard this notice. It may take 1-2 business days for payments to be reflected in our system.</p>

        <p>If you are experiencing financial difficulties, please contact us immediately to discuss payment options or arrangements.</p>

        <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin-top: 20px; border-radius: 0 4px 4px 0;">
            <p style="margin: 0; font-size: 14px; color: #004085;">
                <strong>📎 Attachment:</strong> A copy of your invoice is attached to this email for your reference.
            </p>
        </div>
    </div>
@endsection

@section('disclaimer')
    This email was sent to {{ $tenant->email }} regarding your outstanding invoice with {{ $companyName }}.
    <br><br>
    For inquiries or to make a payment, contact <a href="mailto:{{ $companyEmail }}" style="color: #ba9779 !important; text-decoration: none;">{{ $companyEmail }}</a>{{ !empty($companyPhone) ? ' or call ' . $companyPhone : '' }}.
    <br><br>
    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
@endsection
