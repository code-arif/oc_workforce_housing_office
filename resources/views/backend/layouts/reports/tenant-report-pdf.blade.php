<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Report</title>
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
            width: 25%;
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
        .summary-table .value.success { color: #198754; }
        .summary-table .value.info { color: #0dcaf0; }
        .summary-table .value.warning { color: #ffc107; }
        
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
            background: #2c3e50;
            color: #fff;
        }
        
        .report-table td {
            padding: 6px;
            border-bottom: 1px solid #e9ecef;
            font-size: 9px;
        }
        
        .report-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .report-table tr:hover {
            background: #e9ecef;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-approved { background: #cce5ff; color: #004085; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-under_review { background: #e2e3e5; color: #383d41; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
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
        <h1>Tenant Report</h1>
        <div class="subtitle">Tenant Directory & Status Summary</div>
        <div class="generated">Generated on: {{ $generatedAt }}</div>
    </div>

    @if(count($filters) > 0)
    <div class="filters-section">
        <h3>Applied Filters</h3>
        @if(isset($filters['status']))
            <p><strong>Status:</strong> {{ $filters['status'] }}</p>
        @endif
        @if(isset($filters['property']))
            <p><strong>Property:</strong> {{ $filters['property'] }}</p>
        @endif
        @if(isset($filters['application_source']))
            <p><strong>Application Source:</strong> {{ $filters['application_source'] }}</p>
        @endif
        @if(isset($filters['date_from']))
            <p><strong>Move-in From:</strong> {{ $filters['date_from'] }}</p>
        @endif
        @if(isset($filters['date_to']))
            <p><strong>Move-in To:</strong> {{ $filters['date_to'] }}</p>
        @endif
    </div>
    @endif

    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Total Tenants</span>
                    <span class="value primary">{{ $summary['total_tenants'] }}</span>
                </td>
                <td>
                    <span class="label">Active Tenants</span>
                    <span class="value success">{{ $summary['active_tenants'] }}</span>
                </td>
                <td>
                    <span class="label">Approved Tenants</span>
                    <span class="value info">{{ $summary['approved_tenants'] }}</span>
                </td>
                <td>
                    <span class="label">Pending Tenants</span>
                    <span class="value warning">{{ $summary['pending_tenants'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Tenant Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Property</th>
                <th>Unit</th>
                <th>Move-in Date</th>
                <th>Lease Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
            <tr>
                <td>{{ $row['tenant_name'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td>{{ $row['phone'] }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '_', $row['status'])) }}">
                        {{ $row['status'] }}
                    </span>
                </td>
                <td>{{ $row['property_name'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td>{{ $row['move_in_date'] }}</td>
                <td>{{ $row['lease_status'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                    No data available for the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was automatically generated by the Property Management System.</p>
        <p>Report contains {{ $summary['total_tenants'] }} tenant record(s).</p>
    </div>
</body>
</html>
