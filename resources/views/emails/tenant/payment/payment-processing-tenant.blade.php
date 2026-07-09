@extends('emails.layout.mail')

@section('content')
    <!-- Title -->
    <h2 style="margin:0 0 10px 0;font-size:22px;font-weight:700;color:#333;text-align:center;">
        Payment Processing
    </h2>

    <p style="margin:0 0 16px 0;">
        Dear {{ $data['tenant_name'] }},
    </p>

    <p style="margin:0 0 16px 0;">
        Your payment has been received and is currently <strong>being processed</strong> via ACH Bank Transfer.
        Please allow <strong>3–5 business days</strong> for the transfer to complete.
    </p>

    <!-- Amount Box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0;">
        <tr>
            <td align="center"
                style="background:#fff8f0;border-left:4px solid #e8a040;padding:20px;font-size:26px;font-weight:bold;color:#e8a040;">
                ${{ number_format($data['payment_amount'], 2) }}
            </td>
        </tr>
    </table>

    <!-- Status Notice -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;">
        <tr>
            <td style="background:#fff3cd;border:1px solid #ffc107;border-radius:4px;padding:14px;color:#856404;font-size:14px;">
                ⏳ <strong>Processing:</strong> Your ACH bank transfer is underway. You will receive a confirmation email
                once the payment is fully settled (typically 3–5 business days).
                <br><br>
                <strong>You do not need to make another payment for this invoice during this time.</strong>
            </td>
        </tr>
    </table>

    <!-- Payment Details -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:20px 0;border-collapse:collapse;">
        <tr>
            <td colspan="2" style="background:#f2f2f2;padding:12px;font-weight:bold;border-bottom:2px solid #ddd;">
                Payment Details
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Invoice Number:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['invoice_number'] }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Payment Date:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ date('F d, Y', strtotime($data['payment_date'])) }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Payment Method:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ACH Bank Transfer
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Status:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                <span style="color:#e8a040;font-weight:bold;">Processing</span>
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Expected Settlement:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                3–5 Business Days
            </td>
        </tr>
    </table>

    <!-- Property Details -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:20px 0;border-collapse:collapse;">
        <tr>
            <td colspan="2" style="background:#f2f2f2;padding:12px;font-weight:bold;border-bottom:2px solid #ddd;">
                Property Details
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Property:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['property_name'] }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Unit:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['unit'] }}
            </td>
        </tr>
    </table>

    <p style="margin-top:20px;">
        Once your payment is fully processed and confirmed, you will receive a separate confirmation email.
        If you have any questions, please don't hesitate to contact us.
    </p>
@endsection
