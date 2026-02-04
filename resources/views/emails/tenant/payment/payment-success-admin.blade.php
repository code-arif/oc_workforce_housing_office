<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Payment Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #2196F3;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .notification-icon {
            text-align: center;
            font-size: 48px;
            color: #2196F3;
            margin: 20px 0;
        }

        .details-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .details-table th {
            background-color: #f2f2f2;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #2196F3;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #e3f2fd;
            border-radius: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>New Payment Received</h1>
    </div>

    <div class="content">
        <div class="notification-icon">💰</div>

        <h2 style="text-align: center;">Payment Notification</h2>

        <p>A new payment has been successfully processed through Stripe.</p>

        <div class="amount">
            ${{ number_format($data['payment_amount'], 2) }}
        </div>

        <table class="details-table">
            <tr>
                <th colspan="2">Payment Information</th>
            </tr>
            <tr>
                <td><strong>Payment Number:</strong></td>
                <td>{{ $data['payment']->payment_number }}</td>
            </tr>
            <tr>
                <td><strong>Invoice Number:</strong></td>
                <td>{{ $data['invoice_number'] }}</td>
            </tr>
            <tr>
                <td><strong>Amount Paid:</strong></td>
                <td>${{ number_format($data['payment_amount'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Payment Date:</strong></td>
                <td>{{ date('F d, Y', strtotime($data['payment_date'])) }}</td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td>Credit Card (Stripe)</td>
            </tr>
            <tr>
                <td><strong>Payment Type:</strong></td>
                <td>{{ ucfirst($data['invoice']->type) }}</td>
            </tr>
            <tr>
                <td><strong>Transaction ID:</strong></td>
                <td><span class="highlight">{{ $data['payment']->reference_number }}</span></td>
            </tr>
        </table>

        <table class="details-table">
            <tr>
                <th colspan="2">Tenant Information</th>
            </tr>
            <tr>
                <td><strong>Tenant Name:</strong></td>
                <td>{{ $data['tenant_name'] }}</td>
            </tr>
            <tr>
                <td><strong>Tenant Email:</strong></td>
                <td>{{ $data['tenant']->email }}</td>
            </tr>
            @if ($data['tenant']->profile && $data['tenant']->profile->phone)
                <tr>
                    <td><strong>Tenant Phone:</strong></td>
                    <td>{{ $data['tenant']->profile->phone }}</td>
                </tr>
            @endif
        </table>

        <table class="details-table">
            <tr>
                <th colspan="2">Property Details</th>
            </tr>
            <tr>
                <td><strong>Property:</strong></td>
                <td>{{ $data['property_name'] }}</td>
            </tr>
            <tr>
                <td><strong>Unit:</strong></td>
                <td>{{ $data['unit'] }}</td>
            </tr>
        </table>

        <table class="details-table">
            <tr>
                <th colspan="2">Invoice Status</th>
            </tr>
            <tr>
                <td><strong>Invoice Status:</strong></td>
                <td><strong>{{ ucfirst($data['invoice']->status) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Total Paid:</strong></td>
                <td>${{ number_format($data['invoice']->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Balance Remaining:</strong></td>
                <td>${{ number_format($data['invoice']->balance_due, 2) }}</td>
            </tr>
        </table>

        @if ($data['invoice']->balance_due <= 0)
            <div
                style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <strong>✓ Invoice Fully Paid</strong> - This invoice has been completely paid off.
            </div>
        @else
            <div
                style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <strong>⚠ Partial Payment</strong> - Remaining balance:
                ${{ number_format($data['invoice']->balance_due, 2) }}
            </div>
        @endif

        <p style="margin-top: 30px;">This payment has been automatically recorded in the system.</p>
    </div>

    <div class="footer">
        <p>This is an automated notification from the Property Management System.</p>
        <p>&copy; {{ date('Y') }} Property Management System. All rights reserved.</p>
    </div>
</body>

</html>
