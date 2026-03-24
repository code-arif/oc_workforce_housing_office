@extends('emails.layout.mail')

@section('content')
    <!-- Title -->
    <h2 style="margin:0 0 10px 0;font-size:22px;font-weight:700;color:#333;text-align:center;">
        Payment Successful!
    </h2>

    <p style="margin:0 0 16px 0;">
        Dear {{ $data['tenant_name'] }},
    </p>

    <p style="margin:0 0 16px 0;">
        Thank you for your payment. Your transaction has been successfully processed.
    </p>

    <!-- Amount Box -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0;">
        <tr>
            <td align="center"
                style="background:#f9f9f9;border-left:4px solid #ba9779;padding:20px;font-size:26px;font-weight:bold;color:#ba9779;">
                ${{ number_format($data['payment_amount'], 2) }}
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
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Payment Number:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['payment']->payment_number }}
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
                Credit Card (Stripe)
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Payment Type:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ ucfirst($data['invoice']->type) }}
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


    <!-- Invoice Status -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:20px 0;border-collapse:collapse;">
        <tr>
            <td colspan="2" style="background:#f2f2f2;padding:12px;font-weight:bold;border-bottom:2px solid #ddd;">
                Invoice Status
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Invoice Status:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ ucfirst($data['invoice']->status) }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Total Paid:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ${{ number_format($data['invoice']->paid_amount, 2) }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Balance Due:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ${{ number_format($data['invoice']->balance_due, 2) }}
            </td>
        </tr>
    </table>

    <p style="margin-top:20px;">
        If you have any questions about this payment, please don't hesitate to contact us.
    </p>
@endsection
