<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report</title>
    <style>
        * { margin: 8px; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', Arial, sans-serif;
            font-size: 8px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1a5f7a;
        }
        .header h1 { font-size: 20px; color: #1a5f7a; margin-bottom: 0px; }
        .header .subtitle { font-size: 11px; color: #6c757d; }
        .header .generated { font-size: 9px; color: #888; margin-top: 0px; }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .report-table th {
            background: #1a5f7a;
            color: #fff;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .report-table th.text-right { text-align: right; }
        .report-table td {
            padding: 4px;
            border-bottom: 1px solid #e9ecef;
            font-size: 8px;
        }
        .report-table td.text-right { text-align: right; }
        .report-table tr:nth-child(even) { background: #f0f8ff; }
        .report-table tfoot td {
            font-weight: bold;
            background: #e9ecef;
            padding: 6px 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-reviewed  { background: #cce5ff; color: #004085; }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-disputed  { background: #f8d7da; color: #721c24; }
        .status-voided    { background: #f8d7da; color: #721c24; text-decoration: line-through; }
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 8px;
            color: #888;
        }
        .page-break { page-break-after: always; }
        del { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transaction Report</h1>
        <div class="subtitle">All Payment Transactions with Review Status</div>
        <div class="generated">Generated on: {{ $generatedAt }}</div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Payment #</th>
                <th>Date</th>
                <th>Tenant</th>
                <th>Property</th>
                <th class="text-right">Amount ($)</th>
                <th>Method</th>
                <th>Type</th>
                <th>Invoice</th>
                <th>Paid By</th>
                <th>Proc. Fee</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $row['payment_number'] }}</td>
                    <td>{{ $row['payment_date'] }}</td>
                    <td>{{ $row['tenant_name'] }}</td>
                    <td>{{ $row['property_name'] }}</td>
                    <td class="text-right">
                        @if($row['status'] === 'voided')
                            <del>${{ $row['amount'] }}</del>
                        @else
                            ${{ $row['amount'] }}
                        @endif
                    </td>
                    <td>{{ $row['payment_method'] }}</td>
                    <td>{{ $row['payment_type'] }}</td>
                    <td>{{ $row['invoice_number'] }}</td>
                    <td>{{ $row['paid_by'] }}</td>
                    <td class="text-right">{{ $row['processing_fee'] }}</td>
                    <td>
                        @php
                            $sClass = match (strtolower($row['review_status'])) {
                                'confirmed' => 'status-confirmed',
                                'reviewed' => 'status-reviewed',
                                'disputed' => 'status-disputed',
                                'voided' => 'status-voided',
                                default => 'status-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $sClass }}">{{ $row['review_status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:20px;color:#888;">
                        No transaction data available for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (count($reportData) > 0)
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">TOTAL COLLECTED:</td>
                    <td class="text-right">${{ number_format($summary['total_active_amount'] ?? 0, 2) }}</td>
                    <td colspan="4"></td>
                    <td class="text-right">${{ number_format($summary['total_processing_fees'] ?? 0, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>This report was automatically generated by the Property Management System.</p>
        <p>Report contains {{ $summary['total_all_payments'] ?? 0 }} payment record(s).</p>
        <p style="margin-top:4px;">
            <strong>Collected:</strong> ${{ number_format($summary['total_active_amount'] ?? 0, 2) }} |
            <strong>Fees:</strong> ${{ number_format($summary['total_processing_fees'] ?? 0, 2) }} |
            <strong>Net:</strong> ${{ number_format(max(0, ($summary['total_active_amount'] ?? 0) - ($summary['total_processing_fees'] ?? 0)), 2) }} |
            <strong>Voided:</strong> ${{ number_format($summary['total_voided_amount'] ?? 0, 2) }}
        </p>
        <p style="margin-top:4px;">
            <strong>Pending:</strong> {{ $summary['pending_review'] ?? 0 }} (${{ number_format($summary['pending_amount'] ?? 0, 2) }}) |
            <strong>Confirmed:</strong> {{ $summary['confirmed'] ?? 0 }} (${{ number_format($summary['confirmed_amount'] ?? 0, 2) }}) |
            <strong>Disputed:</strong> {{ $summary['disputed'] ?? 0 }} (${{ number_format($summary['disputed_amount'] ?? 0, 2) }})
        </p>
    </div>
</body>
</html>
