<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Collection Report</title>
    <style>
        * {
            margin: 10px;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #1a5f7a;
        }

        .header h1 {
            font-size: 22px;
            color: #1a5f7a;
            margin-bottom: 0px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #6c757d;
        }

        .header .generated {
            font-size: 10px;
            color: #888;
            margin-top: 0px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .report-table th {
            background: #1a5f7a;
            color: #fff;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .report-table th.text-right {
            text-align: right;
        }

        .report-table td {
            padding: 0px;
            border-bottom: 1px solid #e9ecef;
            font-size: 10px;
        }

        .report-table td.text-right {
            text-align: right;
        }

        .report-table tr:nth-child(even) {
            background: #f0f8ff;
        }

        .report-table tfoot td {
            font-weight: bold;
            background: #e9ecef;
            padding: 8px 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-reviewed {
            background: #cce5ff;
            color: #004085;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-disputed {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 9px;
            color: #888;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Rent Collection Report</h1>
        <div class="subtitle">Payment Collection Summary with Review Status</div>
        <div class="generated">Generated on: {{ $generatedAt }}</div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Payment #</th>
                <th>Property</th>
                <th>Bed</th>
                <th>Tenant</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Stripe Amount</th>
                <th>Stripe Fees</th>
                <th>Stripe Method</th>
                <th>Method</th>
                <th>Reference</th>
                <th>Invoice #</th>
                <th>Status</th>
                <th>Reviewed By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $row['payment_number'] }}</td>
                    <td>{{ $row['property_name'] }}</td>
                    <td>{{ $row['bed_label'] }}</td>
                    <td>{{ $row['tenant_name'] }}</td>
                    <td>{{ $row['payment_date'] }}</td>
                    <td>{{ $row['payment_method'] }}</td>
                    <td>{{ $row['reference_number'] }}</td>
                    <td>{{ $row['invoice_number'] }}</td>
                    <td>
                        @php
                            $statusClass = match (strtolower($row['review_status'])) {
                                'confirmed' => 'status-confirmed',
                                'reviewed' => 'status-reviewed',
                                'disputed' => 'status-disputed',
                                default => 'status-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $row['review_status'] }}</span>
                    </td>
                    <td>{{ $row['reviewed_by'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align: center; padding: 20px; color: #888;">
                        No payment data available for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (count($reportData) > 0)
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;">TOTAL COLLECTED:</td>
                    <td class="text-right">${{ number_format($summary['total_collected'], 2) }}</td>
                    <td class="text-right">${{ number_format($summary['total_stripe_amount'] ?? 0, 2) }}</td>
                    <td class="text-right">${{ number_format($summary['total_stripe_fees'] ?? 0, 2) }}</td>
                    <td colspan="6"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>This report was automatically generated by the Property Management System.</p>
        <p>Report contains {{ $summary['total_payments'] }} payment record(s).</p>
        <p style="margin-top: 5px;">
            <strong>Confirmed:</strong> ${{ number_format($summary['confirmed_total'], 2) }} |
            <strong>Pending:</strong> ${{ number_format($summary['pending_total'], 2) }} |
            <strong>Disputed:</strong> ${{ number_format($summary['disputed_total'], 2) }}
        </p>
    </div>
</body>

</html>
