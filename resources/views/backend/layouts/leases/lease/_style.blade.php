
<style>
    .select2-container {
        width: 100% !important;
    }
    /* Progress Steps */
    .steps-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        padding: 0 20px;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        flex: 1;
    }

    .step-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #adb5bd;
        position: relative;
        z-index: 2;
        transition: all 0.3s;
    }

    .step-item.active .step-circle {
        background: #4285f4;
        border-color: #4285f4;
        color: #fff;
    }

    .step-item.completed .step-circle {
        background: #4285f4;
        border-color: #4285f4;
        color: #fff;
    }

    .step-label {
        margin-top: 10px;
        font-size: 11px;
        font-weight: 600;
        color: #6c757d;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .step-item.active .step-label {
        color: #4285f4;
    }

    .step-line {
        position: absolute;
        top: 30px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #dee2e6;
        z-index: 1;
    }

    .step-item:last-child .step-line {
        display: none;
    }

    .step-item.completed .step-line {
        background: #4285f4;
    }

    /* Card Layout */
    .card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card-body {
        /* display: flex; */
        gap: 30px;
    }

    .step-sidebar {
        width: 100%;
        flex-shrink: 0;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e3f2fd;
        color: #2196F3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .step-info h6 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .step-info p {
        font-size: 14px;
        color: #6c757d;
        margin: 10px 0 10px 0;
    }

    .step-main-content {
        flex: 1;
    }

    /* Lease Type Cards */
    .lease-type-selection {
        margin: 20px 0;
    }

    .lease-type-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        cursor: default;
        transition: all 0.3s;
        height: 100%;
    }

    .lease-type-card.active {
        border-color: #4285f4;
        background: #e3f2fd;
    }

    .lease-type-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .lease-type-header i {
        font-size: 32px;
        color: #4285f4;
    }

    .lease-type-header .badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    .lease-type-card h6 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    /* Selected Property Info Alert */
    .alert-info {
        background: #e3f2fd;
        border-left: 4px solid #2196F3;
        color: #1565c0;
    }

    #fullPropertyPath {
        font-weight: 500;
    }

    /* Sections */
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 20px;
    }

    .deposit-section,
    .rent-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    /* Property Info Banner */
    .property-info-banner {
        background: #e8f5e9;
        padding: 12px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        color: #2e7d32;
        border-left: 4px solid #4caf50;
    }

    /* Unit Summary Card */
    .unit-summary-card {
        background: #fff3cd;
        padding: 15px 20px;
        border-radius: 6px;
        border-left: 4px solid #ffc107;
    }

    /* Tenants Table */
    .tenants-table-container {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .tenants-table-container table {
        margin-bottom: 0;
    }

    .tenants-table-container th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        padding: 10px 8px;
    }

    .tenants-table-container td {
        padding: 10px 8px;
        vertical-align: middle;
    }

    .tenant-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #4285f4;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 600;
    }

    /* Rent Split Section */
    .rent-split-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .rent-split-section .table {
        margin-bottom: 0;
    }

    .rent-split-section th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }

    /* Rental Summary Sidebar */
    .rental-summary-card {
        position: sticky;
        top: 20px;
    }

    .rental-summary-card .card-title {
        font-weight: 600;
        color: #2c3e50;
    }

    .summary-amount h2 {
        font-size: 36px;
        font-weight: 700;
    }

    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .summary-footer {
        border-top: 2px solid #e9ecef;
        padding-top: 15px;
    }

    /* Form Navigation */
    .form-navigation {
        /* position: fixed;
        bottom: 0;
        left: 0;
        right: 0; */
        background: #fff;
        padding: 15px 30px;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 1000;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }

    /* Step Content */
    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
    }

    /* Responsibility Section */
    .responsibility-section .form-check-label {
        cursor: pointer;
    }

    /* Partial Payment Section */
    .partial-payment-section {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
    }

    /* Form Controls */
    .form-control:focus,
    .form-select:focus {
        border-color: #4285f4;
        box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
    }

    /* Responsive */
    @media (max-width: 991px) {
        .card-body {
            flex-direction: column;
            gap: 20px;
        }

        .step-sidebar {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .step-number {
            margin-bottom: 0;
        }

        .steps-container {
            overflow-x: auto;
            padding: 0 10px;
        }

        .step-label {
            font-size: 9px;
        }

        .rental-summary-card {
            position: static;
            margin-top: 20px;
        }
    }

    @media (max-width: 767px) {
        .step-circle {
            width: 50px;
            height: 50px;
            font-size: 20px;
        }

        .step-line {
            top: 25px;
        }

        .tenants-table-container {
            overflow-x: auto;
        }

        .form-navigation {
            flex-wrap: wrap;
            gap: 10px;
        }
    }

    /* Tenant Management Styles */
    .add-tenant-section .card {
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }

    .add-tenant-section .card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        /* transform: translateY(-2px); */
    }

    .add-tenant-section .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 15px;
    }

    .add-tenant-section .card-header h6 {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .add-tenant-section .card-body {
        padding: 15px;
    }

    .tenant-card {
        border: 1px solid #e3f2fd;
        border-left: 4px solid #2196F3;
        transition: all 0.3s;
        height: 100%;
    }

    .tenant-card:hover {
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
        /* transform: translateY(-2px); */
    }

    .tenant-card h6 {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
    }

    .tenant-card .badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    .selected-tenants-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }

    .selected-tenants-section h6 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 15px;
    }

    #selectedTenantsList .card {
        margin-bottom: 15px;
    }

    .unit-summary-card {
        background: #fff3cd;
        padding: 15px 20px;
        border-radius: 6px;
        border-left: 4px solid #ffc107;
    }

    .unit-summary-card h6 {
        font-size: 16px;
        font-weight: 600;
        color: #856404;
    }

    /* Form control improvements */
    .form-control-sm {
        font-size: 13px;
        padding: 6px 10px;
    }

    /* Button improvements */
    .btn-sm {
        font-size: 13px;
        padding: 6px 12px;
    }

    /* Alert improvements */
    .alert-info {
        background: #e3f2fd;
        border-left: 4px solid #2196F3;
        color: #1565c0;
    }

    /* Spinner for loading states */
    .spinner-border-sm {
        width: 14px;
        height: 14px;
        border-width: 2px;
    }

    /* Finalize Lease Section - Minimal Design */
    .finalize-section {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .finalize-section .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .finalize-section .section-header h6 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }

    .finalize-section .section-header .btn-link {
        font-size: 12px;
        padding: 0;
        text-decoration: none;
    }

    .finalize-section .section-content {
        padding: 16px;
    }

    .finalize-section .info-item {
        margin-bottom: 12px;
    }

    .finalize-section .info-item .label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
    }

    .finalize-section .info-item .value {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #2c3e50;
    }

    .finalize-section .tenant-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .finalize-section .tenant-chip {
        display: inline-flex;
        align-items: center;
        background: #e3f2fd;
        color: #1565c0;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .finalize-section .tenant-chip i {
        margin-right: 6px;
        font-size: 12px;
    }

    /* Create Lease Button */
    #createLeaseBtn {
        min-width: 140px;
    }

    #saveDraftBtn {
        min-width: 120px;
    }
</style>