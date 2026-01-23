<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Report</title>
    <style>
        * {
            margin: 10px;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #D9A600;
        }
        
        .header h1 {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #6c757d;
        }
        
        .header .generated {
            font-size: 10px;
            color: #888;
            margin-top: 5px;
        }
        
        .filters-section {
            background: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 4px solid #D9A600;
        }
        
        .filters-section h3 {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .filters-section p {
            font-size: 10px;
            margin: 2px 0;
        }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 10px 15px;
            text-align: center;
            border: 1px solid #e9ecef;
            width: 50%;
        }
        
        .summary-table .label {
            font-size: 9px;
            color: #6c757d;
            text-transform: uppercase;
            display: block;
        }
        
        .summary-table .value {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-top: 3px;
        }
        
        .summary-table .value.primary { color: #0d6efd; }
        .summary-table .value.danger { color: #dc3545; }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .report-table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .report-table th.text-right {
            text-align: right;
        }
        
        .report-table td {
            padding: 6px;
            border-bottom: 1px solid #e9ecef;
            font-size: 9px;
        }
        
        .report-table td.text-right {
            text-align: right;
        }
        
        .report-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .report-table tr:hover {
            background: #e9ecef;
        }
        
        .report-table tfoot td {
            font-weight: bold;
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
        <h1>Rent Report</h1>
        <div class="subtitle">Outstanding Invoice Summary</div>
        <div class="generated">Generated on: {{ $generatedAt }}</div>
    </div>

    @if(count($filters) > 0)
    <div class="filters-section">
        <h3>Applied Filters</h3>
        @if(isset($filters['property']))
            <p><strong>Property:</strong> {{ $filters['property'] }}</p>
        @endif
        @if(isset($filters['tenant']))
            <p><strong>Tenant:</strong> {{ $filters['tenant'] }}</p>
        @endif
        @if(isset($filters['date_from']))
            <p><strong>Due Date From:</strong> {{ $filters['date_from'] }}</p>
        @endif
        @if(isset($filters['date_to']))
            <p><strong>Due Date To:</strong> {{ $filters['date_to'] }}</p>
        @endif
    </div>
    @endif

    <table class="report-table">
        <thead>
            <tr>
                <th>Property Name</th>
                <th>Beds</th>
                <th>Tenant</th>
                <th>Due Date</th>
                <th class="text-right">Outstanding Amount</th>
                <th>Note</th>
                <th>Invoice No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
            <tr>
                <td>{{ $row['property_name'] }}</td>
                <td>{{ $row['bed_label'] }}</td>
                <td>{{ $row['tenant_name'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td class="text-right">${{ $row['outstanding_amount'] }}</td>
                <td>{{ $row['notes'] }}</td>
                <td>{{ $row['invoice_number'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #888;">
                    No data available for the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($reportData) > 0)
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;">TOTAL OUTSTANDING:</td>
                <td class="text-right">${{ number_format($summary['total_outstanding'], 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>This report was automatically generated by the Property Management System.</p>
        <p>Report contains {{ $summary['total_invoices'] }} invoice record(s).</p>
    </div>
</body>
</html>
