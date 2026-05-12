<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }

        /* Header */
        .invoice-header {
            background: #1e3a5f;
            padding: 15px 30px;
            color: #fff;
            margin: -20px -20px 0;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 40%;
        }

        .invoice-title {
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }

        .invoice-number {
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-paid {
            background: rgba(76, 175, 80, 0.25);
            color: #81c784;
        }

        .status-partial {
            background: rgba(255, 193, 7, 0.25);
            color: #ffd54f;
        }

        .status-unpaid {
            background: rgba(255, 193, 7, 0.25);
            color: #ffd54f;
        }

        .status-overdue {
            background: rgba(244, 67, 54, 0.25);
            color: #ef9a9a;
        }

        /* Info Section */
        .info-section {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            vertical-align: top;
            padding: 5px 10px;
        }

        .info-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #1e3a5f;
            font-size: 12px;
        }

        .info-value-sm {
            color: #6c757d;
            font-size: 11px;
        }

        /* Lease Info Banner */
        .lease-banner {
            background: #f8fafc;
            padding: 0px 12px;
            margin: 10px 0;
            border-radius: 8px;
        }

        .lease-banner-table {
            width: 100%;
        }

        .lease-banner-table td {
            padding: 5px 8px;
            border-right: 1px solid #e9ecef;
        }

        .lease-banner-table td:last-child {
            border-right: none;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th {
            background: #f8fafc;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }

        .items-table th.text-center {
            text-align: center;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: top;
        }

        .items-table td.text-center {
            text-align: center;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .item-title {
            font-weight: 600;
            color: #1e3a5f;
            font-size: 13px;
        }

        .item-desc {
            color: #6c757d;
            font-size: 11px;
            margin-top: 3px;
        }

        .deposit-row {
            background: #fffbeb;
        }

        .deposit-badge {
            display: inline-block;
            background: rgba(255, 193, 7, 0.2);
            color: #f59e0b;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 5px;
        }

        /* Summary Section */
        .summary-section {
            margin: 10px 0;
        }

        .summary-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 10px;
        }

        .summary-row {
            border-bottom: 1px solid #e9ecef;
        }

        .summary-label {
            color: #6c757d;
            font-size: 12px;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
            color: #1e3a5f;
        }

        .summary-total {
            border-top: 2px solid #1e3a5f;
            font-size: 14px;
        }

        .summary-total td {
            padding-top: 8px;
            font-weight: bold;
        }

        .summary-balance {
            background: #f8fafc;
        }

        .text-success {
            color: #4caf50;
        }

        .text-danger {
            color: #f44336;
        }

        /* Payment History */
        .payment-history {
            margin: 15px 0;
            padding: 8px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .payment-history-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 15px;
        }

        .payment-history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-history-table th {
            background: #fff;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 1px solid #e9ecef;
        }

        .payment-history-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 11px;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }

        .footer-thanks {
            font-size: 14px;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 5px;
        }

        .footer-generated {
            font-size: 10px;
            color: #6c757d;
        }

        /* Payment Note */
        .payment-note {
            padding: 15px 20px;
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            margin: 20px 0;
        }

        .payment-note.paid {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }

        .payment-note-title {
            font-weight: 600;
            color: #f57c00;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .payment-note.paid .payment-note-title {
            color: #388e3c;
        }

        .payment-note-text {
            color: #6c757d;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="header-right">
                    @if($invoice->status == 'PAID')
                        <span class="status-badge status-paid">✓ Paid</span>
                    @elseif($invoice->status == 'PARTIAL')
                        <span class="status-badge status-partial">Partially Paid</span>
                    @elseif($invoice->isOverdue())
                        <span class="status-badge status-overdue">! Overdue</span>
                    @else
                        <span class="status-badge status-unpaid">Unpaid</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td style="width: 33%;">
                        <div class="info-label">From</div>
                        <div class="info-value">{{ $lease->property ? $lease->property->name : 'N/A' }}</div>
                        <div class="info-value-sm">{{ config('app.name', 'OC Workforce') }}</div>
                        @if($lease->property)
                        <div class="info-value-sm">{{ $lease->property->address }}</div>
                        @endif
                    </td>
                    <td style="width: 33%;">
                        <div class="info-label">Bill To</div>
                        @php
                            $profile = $lease->tenant ? $lease->tenant->profile : null;
                            $tenantName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'N/A';
                        @endphp
                        <div class="info-value">{{ $tenantName }}</div>
                        <div class="info-value-sm">{{ $lease->tenant ? $lease->tenant->email : 'N/A' }}</div>
                        @if($profile && $profile->phone)
                        <div class="info-value-sm">{{ $profile->phone }}</div>
                        @endif
                    </td>
                    <td style="width: 33%; text-align: right;">
                        <div style="margin-bottom: 10px;">
                            <div class="info-label">Issue Date</div>
                            <div class="info-value">{{ $invoice->created_at->format('M d, Y') }}</div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <div class="info-label">Due Date</div>
                            <div class="info-value {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                {{ date('M d, Y', strtotime($invoice->due_date)) }}
                            </div>
                        </div>
                        @if($invoice->paid_at)
                        <div>
                            <div class="info-label">Paid On</div>
                            <div class="info-value text-success">{{ date('M d, Y', strtotime($invoice->paid_at)) }}</div>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Lease Info Banner -->
        <div class="lease-banner">
            <table class="lease-banner-table">
                <tr>
                    <td style="width: 33%;">
                        <div class="info-label">Unit</div>
                        <div class="info-value" style="font-size: 12px;">
                            {{ $lease->assignments->where('is_current', true)->first() && $lease->assignments->where('is_current', true)->first()->bed ? $lease->assignments->where('is_current', true)->first()->bed->bed_label : 'N/A' }}
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="info-label">Rent Period</div>
                        <div class="info-value" style="font-size: 12px;">{{ date('M Y', strtotime($invoice->due_date)) }}</div>
                    </td>
                    <td style="width: 33%;">
                        <div class="info-label">Payment Frequency</div>
                        <div class="info-value" style="font-size: 12px;">{{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-center" style="width: 15%;">Rate</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if ($invoice->type !== 'ITEM_SALE')
                <!-- Rent Item -->
                <tr>
                    <td class="item-desc">
                        <strong>{{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }} Rent</strong>
                        <p class="text-muted small mb-0">Rent payment for {{ date('F Y', strtotime($invoice->due_date)) }}</p>
                    </td>
                    <td class="text-center">1</td>
                    <td class="text-center">${{ number_format($invoice->amount, 2) }}</td>
                    <td class="text-right">${{ number_format($invoice->amount, 2) }}</td>
                </tr>


            @else
                @foreach ($invoice->items as $item)
                    <tr>
                        <td class="item-desc">
                            <strong>{{ $item->item->name }}</strong>
                            @if($item->description)
                            <p class="text-muted small mb-0">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">${{ number_format($item->item->price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr class="summary-row">
                    <td class="summary-label">Rent Amount</td>
                    <td class="summary-value">${{ number_format($invoice->amount, 2) }}</td>
                </tr>
                


                <tr class="summary-total">
                    <td class="summary-label">Total Invoice Amount</td>
                    <td class="summary-value">${{ number_format($totalDue, 2) }}</td>
                </tr>

                @if($totalPaid > 0)
                <tr class="summary-row">
                    <td class="summary-label">Amount Paid</td>
                    <td class="summary-value text-success">${{ number_format($totalPaid, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-balance">
                    <td class="summary-label" style="font-weight: bold;">Balance Due</td>
                    <td class="summary-value {{ $balanceDue == 0 ? 'text-success' : 'text-danger' }}">${{ number_format($balanceDue, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment History -->
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div class="payment-history">
            <div class="payment-history-title">Payment History</div>
            <table class="payment-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ date('M d, Y', strtotime($payment->payment_date)) }}</td>
                        <td class="text-success" style="font-weight: bold;">${{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                        <td>{{ $payment->note ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Payment Note -->
        @if($invoice->status !== 'PAID')
        <div class="payment-note">
            <div class="payment-note-title">Payment Information</div>
            <div class="payment-note-text">
                Please ensure payment is made by the due date to avoid late fees. 
                For questions regarding this invoice, please contact our office.
            </div>
        </div>
        @else
        <div class="payment-note paid">
            <div class="payment-note-title">Payment Received</div>
            <div class="payment-note-text">
                Thank you for your payment. This invoice has been paid in full on {{ date('M d, Y', strtotime($invoice->paid_at)) }}.
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-thanks">Thank you for your business!</div>
            <div class="footer-generated">Generated on {{ now()->format('M d, Y \a\t h:i A') }}</div>
        </div>
    </div>
</body>
</html>
