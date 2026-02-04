<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
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
            background-color: #4CAF50;
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

        .success-icon {
            text-align: center;
            font-size: 48px;
            color: #4CAF50;
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
            color: #4CAF50;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Payment Confirmation</h1>
    </div>

    <div class="content">
        <div class="success-icon">✓</div>

        <h2 style="text-align: center;">Payment Successful!</h2>

        <p>Dear {{ $data['tenant_name'] }},</p>

        <p>Thank you for your payment. Your transaction has been successfully processed.</p>

        <div class="amount">
            ${{ number_format($data['payment_amount'], 2) }}
        </div>

        <table class="details-table">
            <tr>
                <th colspan="2">Payment Details</th>
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
                <td>{{ ucfirst($data['invoice']->status) }}</td>
            </tr>
            <tr>
                <td><strong>Total Paid:</strong></td>
                <td>${{ number_format($data['invoice']->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Balance Due:</strong></td>
                <td>${{ number_format($data['invoice']->balance_due, 2) }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">If you have any questions about this payment, please don't hesitate to contact us.
        </p>

        <p>Best regards,<br>Property Management Team</p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} Property Management System. All rights reserved.</p>
    </div>
</body>

</html>
