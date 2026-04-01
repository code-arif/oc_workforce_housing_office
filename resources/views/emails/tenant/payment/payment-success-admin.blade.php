@extends('emails.layout.mail')

@section('content')
    <h2 style="margin:0 0 10px 0;font-size:22px;font-weight:700;color:#333;text-align:center;">
        New Payment Received
    </h2>

    <p style="margin:0 0 16px 0;">
        A new payment has been successfully processed through Stripe.
    </p>

    <!-- Amount -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:20px 0;">
        <tr>
            <td align="center"
                style="background:#f9f9f9;border-left:4px solid #ba9779;padding:20px;font-size:26px;font-weight:bold;color:#ba9779;">
                ${{ number_format($data['payment_amount'], 2) }}
            </td>
        </tr>
    </table>


    <!-- Payment Information -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:20px 0;border-collapse:collapse;">
        <tr>
            <td colspan="2" style="background:#f2f2f2;padding:12px;font-weight:bold;border-bottom:2px solid #ddd;">
                Payment Information
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
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Amount Paid:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ${{ number_format($data['payment_amount'], 2) }}
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

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Transaction ID:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                <span style="background:#f9f9f9;padding:4px 8px;border-radius:3px;">
                    {{ $data['payment']->reference_number }}
                </span>
            </td>
        </tr>
    </table>


    <!-- Tenant Information -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="margin:20px 0;border-collapse:collapse;">
        <tr>
            <td colspan="2" style="background:#f2f2f2;padding:12px;font-weight:bold;border-bottom:2px solid #ddd;">
                Tenant Information
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Tenant Name:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['tenant_name'] }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Tenant Email:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                {{ $data['tenant']->email }}
            </td>
        </tr>

        @if ($data['tenant']->profile && $data['tenant']->profile->phone)
            <tr>
                <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Tenant Phone:</strong></td>
                <td style="padding:10px;border-bottom:1px solid #ddd;">
                    {{ $data['tenant']->profile->phone }}
                </td>
            </tr>
        @endif
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
                <strong>{{ ucfirst($data['invoice']->status) }}</strong>
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Total Paid:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ${{ number_format($data['invoice']->paid_amount, 2) }}
            </td>
        </tr>

        <tr>
            <td style="padding:10px;border-bottom:1px solid #ddd;"><strong>Balance Remaining:</strong></td>
            <td style="padding:10px;border-bottom:1px solid #ddd;">
                ${{ number_format($data['invoice']->balance_due, 2) }}
            </td>
        </tr>
    </table>


    {{-- status box --}}
    @if ($data['invoice']->balance_due <= 0)
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
            <tr>
                <td style="background:#e8f5e9;padding:15px;border-left:4px solid #2e7d32;">
                    <strong>✓ Invoice Fully Paid</strong> - This invoice has been completely paid off.
                </td>
            </tr>
        </table>
    @else
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
            <tr>
                <td style="background:#fff8e1;padding:15px;border-left:4px solid #f57c00;">
                    <strong>⚠ Partial Payment</strong> - Remaining balance:
                    ${{ number_format($data['invoice']->balance_due, 2) }}
                </td>
            </tr>
        </table>
    @endif


    <p style="margin-top:20px;">
        This payment has been automatically recorded in the system.
    </p>
@endsection
