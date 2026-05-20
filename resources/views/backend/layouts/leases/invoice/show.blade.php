@extends('backend.app')

@section('title', 'Invoice Details')

@section('content')
    {{-- MIAN INVOICE CARD --}}
    <div class="app-content main-content mt-0 mb-5">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Page Header --}}
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">Invoice
                                {{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('leases.index') }}">Leases</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('leases.show', $invoice->lease_id) }}">Lease Details</a></li>
                                    <li class="breadcrumb-item active">Invoice</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    <div class="d-flex gap-2 flex-wrap">

                        <a href="{{ route('invoices.index') }}"
                            class="btn btn-outline-success d-flex align-items-center justify-content-center">
                            <i class="fe fe-arrow-left me-2"></i>
                            <span>Back to invoices</span>
                        </a>

                        <a href="{{ route('leases.show', $invoice->lease_id) }}"
                            class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                            <i class="fe fe-arrow-left me-2"></i>
                            <span>Back to Lease</span>
                        </a>

                        <a href="{{ route('invoices.download.pdf', $invoice->id) }}"
                            class="btn btn-outline-primary d-flex align-items-center justify-content-center"
                            target="_blank">
                            <i class="fe fe-download me-2"></i>
                            <span>Download PDF</span>
                        </a>

                        @if ($canCancelPaidCash)
                            <button class="btn btn-outline-danger d-flex align-items-center justify-content-center"
                                onclick="showCancelPaidCashModal()" title="Void paid cash invoice">
                                <i class="fe fe-slash me-2"></i>
                                <span>Void Paid Invoice</span>
                            </button>
                        @endif

                        @if ($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                            <button class="btn btn-outline-warning d-flex align-items-center justify-content-center"
                                onclick="showEditForm()" title="Edit invoice details">
                                <i class="fe fe-edit me-2"></i>
                                <span>Edit Invoice</span>
                            </button>

                            <button class="btn btn-outline-danger d-flex align-items-center justify-content-center"
                                onclick="showVoidModal()" title="Void / Cancel this invoice">
                                <i class="fe fe-slash me-2"></i>
                                <span>Void Invoice</span>
                            </button>
                        @endif

                        @if ($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                            <button class="btn btn-success d-flex align-items-center justify-content-center"
                                onclick="showPaymentForm()">
                                <i class="fe fe-dollar-sign me-2"></i>
                                <span>Make Payment</span>
                            </button>

                            {{-- @elseif($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED' && !$canMakePayment)
                                <button class="btn btn-secondary d-flex align-items-center justify-content-center"
                                    disabled
                                    title="Previous invoice must be paid first">
                                    <i class="fe fe-lock me-2"></i>
                                    <span>Pay Previous Invoice First</span>
                                </button> --}}
                        @endif

                    </div>
                </div>

                <!-- Invoice Container -->
                <div class="row">
                    <div class="col-lg-7 invoice-container">
                        <div class="invoice-paper">

                            <!-- Invoice Header -->
                            <div class="invoice-header">
                                <div class="invoice-brand">
                                    <h1>INVOICE</h1>
                                    <span
                                        class="invoice-number">{{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="invoice-status">
                                    @if ($invoice->status == 'PAID')
                                        <span class="status-badge paid">
                                            <i class="fe fe-check-circle me-1"></i> Paid
                                        </span>
                                    @elseif($invoice->status == 'PARTIAL')
                                        <span class="status-badge paid">
                                            <i class="fe fe-credit-card me-1"></i> Partially Paid
                                        </span>
                                    @elseif($invoice->status == 'CANCELLED')
                                        <span class="status-badge cancelled">
                                            <i class="fe fe-slash me-1"></i> Voided
                                        </span>
                                    @elseif($invoice->isOverdue())
                                        <span class="status-badge overdue">
                                            <i class="fe fe-alert-circle me-1"></i> Overdue
                                        </span>
                                    @else
                                        <span class="status-badge unpaid">
                                            <i class="fe fe-clock me-1"></i> Unpaid
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Invoice Info Grid -->
                            <div class="invoice-info-grid">
                                <div class="info-block">
                                    <label>From</label>
                                    <h5>{{ $lease->property ? $lease->property->name : 'N/A' }}</h5>
                                    <p class="text-muted mb-0">{{ config('app.name', 'OC Workforce') }}</p>
                                    @if ($lease->property)
                                        <p class="text-muted small mb-0">{{ $lease->property->address }}</p>
                                    @endif
                                </div>
                                <div class="info-block">
                                    <label>Bill To</label>
                                    @php
                                        $profile = $lease->tenant ? $lease->tenant->profile : null;
                                        $tenantName = $profile
                                            ? trim($profile->first_name . ' ' . ($profile->last_name ?? ''))
                                            : 'N/A';
                                    @endphp
                                    <h5>{{ $tenantName }}</h5>
                                    <p class="text-muted mb-0">{{ $lease->tenant ? $lease->tenant->email : 'N/A' }}</p>
                                    <p class="text-muted small mb-0">{{ $profile ? $profile->phone : '' }}</p>
                                </div>
                                <div class="info-block text-end">
                                    <div class="date-row">
                                        <label>Issue Date</label>
                                        <span>{{ $invoice->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="date-row">
                                        <label>Due Date</label>
                                        <span class="{{ $invoice->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                            {{ date('M d, Y', strtotime($invoice->due_date)) }}
                                        </span>
                                    </div>
                                    @if ($invoice->paid_at)
                                        <div class="date-row">
                                            <label>Paid On</label>
                                            <span
                                                class="text-success">{{ date('M d, Y', strtotime($invoice->paid_at)) }}</span>
                                        </div>
                                    @endif
                                    @if ($invoice->cancelled_at)
                                        <div class="date-row">
                                            <label>Voided On</label>
                                            <span
                                                class="text-danger">{{ date('M d, Y', strtotime($invoice->cancelled_at)) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($invoice->status === 'CANCELLED')
                                <!-- Voided Banner -->
                                <div class="voided-banner">
                                    <i class="fe fe-slash me-2"></i>
                                    <strong>This invoice has been VOIDED</strong>
                                    @if ($invoice->cancelled_reason)
                                        <span class="ms-2 text-muted">— Reason: {{ $invoice->cancelled_reason }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Lease Info Banner -->
                            <div class="lease-info-banner">
                                <div class="banner-item">
                                    <i class="fe fe-home"></i>
                                    <div>
                                        <label>Unit</label>
                                        <span>{{ $lease->assignments->where('is_current', true)->first() && $lease->assignments->where('is_current', true)->first()->bed ? $lease->assignments->where('is_current', true)->first()->bed->bed_label : 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="banner-item">
                                    <i class="fe fe-calendar"></i>
                                    <div>
                                        <label>Rent Period</label>
                                        <span>{{ date('M Y', strtotime($invoice->due_date)) }}</span>
                                    </div>
                                </div>
                                <div class="banner-item">
                                    <i class="fe fe-layers"></i>
                                    <div>
                                        <label>Payment Frequency</label>
                                        <span>{{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items Table -->
                            <div class="invoice-items">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="item-desc">Description</th>
                                            <th class="item-qty">Qty</th>
                                            <th class="item-rate">Rate</th>
                                            <th class="item-amount">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($invoice->type !== 'ITEM_SALE')
                                            <!-- Rent Item -->
                                            <tr>
                                                <td class="item-desc">
                                                    <strong>{{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }}
                                                        Rent</strong>
                                                    <p class="text-muted small mb-0">Rent payment for
                                                        {{ date('F Y', strtotime($invoice->due_date)) }}</p>
                                                </td>
                                                <td class="item-qty">1</td>
                                                <td class="item-rate">${{ number_format($invoice->amount, 2) }}</td>
                                                <td class="item-amount">${{ number_format($invoice->amount, 2) }}</td>
                                            </tr>

                                            <!-- Security Deposit (only for first invoice if includes deposit and not collected) -->
                                            {{-- @if ($isFirstInvoice && $depositInvoice !== null)
                                            <tr class="deposit-row">
                                                <td class="item-desc">
                                                    <strong>Security Deposit</strong>
                                                    <p class="text-muted small mb-0">
                                                        <span class="badge bg-warning-light text-warning">One-time payment</span>
                                                        Refundable security deposit
                                                    </p>
                                                </td>
                                                <td class="item-qty">1</td>
                                                <td class="item-rate">${{ number_format($depositInvoice->amount, 2) }}</td>
                                                <td class="item-amount">${{ number_format($depositInvoice->amount, 2) }}</td>
                                            </tr>
                                            @endif --}}
                                        @else
                                            @foreach ($invoice->items as $item)
                                                <tr>
                                                    <td class="item-desc">
                                                        <strong>{{ $item->item->name }}</strong>
                                                        @if ($item->description)
                                                            <p class="text-muted small mb-0">{{ $item->description }}</p>
                                                        @endif
                                                    </td>
                                                    <td class="item-qty">{{ $item->quantity }}</td>
                                                    <td class="item-rate">${{ number_format($item->item->price, 2) }}</td>
                                                    <td class="item-amount">${{ number_format($item->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                    </tbody>
                                </table>
                            </div>

                            <!-- Invoice Summary -->
                            <div class="invoice-summary">
                                <div class="summary-details">
                                    <div class="summary-row">
                                        <span>Rent Amount</span>
                                        <span>${{ number_format($invoice->amount, 2) }}</span>
                                    </div>

                                    {{-- @if ($isFirstInvoice && $depositInvoice !== null)
                                    <div class="summary-row deposit">
                                        <span>
                                            Security Deposit
                                            <span class="badge bg-warning-light text-warning ms-2">Not Collected</span>
                                        </span>
                                        <span>${{ number_format($depositInvoice->amount, 2) }}</span>
                                    </div>
                                    @php
                                        $totalDue +=$depositInvoice->amount;
                                        $balanceDue +=$depositInvoice->amount;
                                    @endphp
                                    @elseif($isFirstInvoice && $depositInvoice === null )
                                    <div class="summary-row deposit collected">
                                        <span>
                                            Security Deposit
                                            <span class="badge bg-success-light text-success ms-2">Collected</span>
                                        </span>
                                        <span class="text-success">${{ number_format($lease->deposit_amount, 2) }}</span>
                                    </div>
                                    @endif --}}

                                    <div class="summary-row total">
                                        <span>Total Invoice Amount</span>
                                        <span>${{ number_format($totalDue, 2) }}</span>
                                    </div>

                                    @if ($totalPaid > 0)
                                        <div class="summary-row paid-amount">
                                            <span>Amount Paid</span>
                                            <span class="text-success">${{ number_format($totalPaid, 2) }}</span>
                                        </div>
                                    @endif

                                    <div
                                        class="summary-row balance {{ $balanceDue == 0 ? 'text-success' : 'text-danger' }}">
                                        <span>Balance Due</span>
                                        <span class="fw-bold">${{ number_format($balanceDue, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History -->
                            @if ($invoice->payments && $invoice->payments->count() > 0)
                                <div class="payment-history mt-4">
                                    <h6 class="mb-3"><i class="fe fe-list me-2"></i>Payment History</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Payment Method</th>
                                                    <th>Note</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoice->payments as $payment)
                                                    <tr>
                                                        <td>{{ date('M d, Y', strtotime($payment->payment_date)) }}</td>
                                                        <td class="text-success fw-bold">
                                                            ${{ number_format($payment->amount, 2) }}</td>
                                                        <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                                        <td>{{ $payment->note ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Payment Info -->
                            @if ($invoice->status !== 'PAID')
                                <div class="payment-info">
                                    <h6><i class="fe fe-info me-2"></i>Payment Information</h6>
                                    <p class="text-muted mb-0">
                                        Please ensure payment is made by the due date to avoid late fees.
                                        For questions regarding this invoice, please contact our office.
                                    </p>
                                </div>
                            @else
                                <div class="payment-info paid">
                                    <h6><i class="fe fe-check-circle me-2"></i>Payment Received</h6>
                                    <p class="text-muted mb-0">
                                        Thank you for your payment. This invoice has been paid in full on
                                        {{ date('M d, Y', strtotime($invoice->paid_at)) }}.
                                    </p>
                                </div>
                            @endif

                            <!-- Invoice Footer -->
                            <div class="invoice-footer">
                                <p>Thank you for your business!</p>
                                <small class="text-muted">Generated on {{ now()->format('M d, Y \a\t h:i A') }}</small>
                            </div>

                        </div>
                    </div>

                    <!-- Payment Form Section -->
                    <div class="col-lg-5" id="paymentSection" style="display: none;">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Make Payment</h5>
                                <button type="button" class="btn-close" onclick="hidePaymentForm()"></button>
                            </div>
                            <div class="card-body">
                                <form id="paymentForm" onsubmit="submitPayment(event)">
                                    @csrf

                                    <!-- Payment Summary -->
                                    <div class="alert alert-info mb-4">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Invoice:</span>
                                            <strong>${{ number_format($totalDue, 2) }}</strong>
                                        </div>
                                        @php
                                            $totalPaid = $invoice->payments ? $invoice->payments->sum('amount') : 0;
                                            $balanceDue = $totalDue - $totalPaid;
                                        @endphp
                                        @if ($totalPaid > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Already Paid:</span>
                                                <strong class="text-success">${{ number_format($totalPaid, 2) }}</strong>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between">
                                            <span>Balance Due:</span>
                                            <strong class="text-danger"
                                                id="balanceDueAmount">${{ number_format($balanceDue, 2) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Payment Amount -->
                                    <div class="mb-3">
                                        <label for="paymentAmount" class="form-label">Payment Amount <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="paymentAmount" name="amount"
                                                min="0.01" step="0.01" max="{{ $balanceDue }}"
                                                value="{{ $balanceDue }}" required oninput="updateRemainingBalance()">
                                        </div>
                                        <div class="form-text">
                                            Maximum: ${{ number_format($balanceDue, 2) }}
                                        </div>
                                    </div>

                                    <!-- Quick Amount Buttons -->
                                    <div class="mb-3">
                                        <label class="form-label">Quick Select</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @if ($balanceDue >= 100)
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="setPaymentAmount({{ min(100, $balanceDue) }})">
                                                    $100
                                                </button>
                                            @endif
                                            @if ($balanceDue >= 500)
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="setPaymentAmount({{ min(500, $balanceDue) }})">
                                                    $500
                                                </button>
                                            @endif
                                            @if ($balanceDue > 0)
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="setPaymentAmount({{ $balanceDue / 2 }})">
                                                    50% (${{ number_format($balanceDue / 2, 2) }})
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                    onclick="setPaymentAmount({{ $balanceDue }})">
                                                    Full (${{ number_format($balanceDue, 2) }})
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Remaining Balance After Payment -->
                                    <div class="alert alert-light mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Remaining Balance:</span>
                                            <strong id="remainingBalance"
                                                class="text-primary">${{ number_format(0, 2) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Payment Date -->
                                    <div class="mb-3">
                                        <label for="paymentDate" class="form-label">Payment Date <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker2" id="paymentDate"
                                            name="payment_date" value="{{ date('m/d/Y') }}" max="{{ date('m/d/Y') }}"
                                            required>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="mb-3">
                                        <label for="paymentMethod" class="form-label">Payment Method <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                                            <option value="">Select payment method</option>
                                            <option value="cash">Cash</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <!-- Reference Number -->
                                    <div class="mb-3">
                                        <label for="referenceNumber" class="form-label">Reference/Transaction
                                            Number</label>
                                        <input type="text" class="form-control" id="referenceNumber"
                                            name="reference_number" placeholder="Optional">
                                    </div>

                                    <!-- Payment Note -->
                                    <div class="mb-4">
                                        <label for="paymentNote" class="form-label">Note</label>
                                        <textarea class="form-control" id="paymentNote" name="note" rows="3"
                                            placeholder="Add any additional notes (optional)"></textarea>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit"
                                            class="btn btn-success d-inline-flex align-items-center justify-content-center">
                                            <i class="fe fe-check me-2"></i>Submit Payment
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center"
                                            onclick="hidePaymentForm()">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- EDIT INVOICE MODAL --}}
    <div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInvoiceModalLabel">
                        <i class="fe fe-edit me-2 text-warning"></i>Edit Invoice <span
                            class="text-primary fw-bold">{{ $invoice->invoice_number }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="editInvoiceForm">
                        @csrf

                        {{-- Due Date --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker2" id="edit_due_date"
                                    name="due_date" value="{{ $invoice->due_date->format('m/d/Y') }}" required>
                            </div>
                            @if ($invoice->type !== 'ITEM_SALE')
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Base Amount ($)
                                        @if ($invoice->is_first_invoice && $invoice->includes_deposit)
                                            <small class="text-muted">(excl. deposit)</small>
                                        @endif
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="edit_amount" name="amount"
                                            value="{{ $invoice->amount }}" min="0" step="0.01">
                                    </div>
                                    @if ($invoice->is_first_invoice && $invoice->includes_deposit)
                                        <div class="form-text">
                                            Deposit (${{ number_format($lease->deposit_amount ?? 0, 2) }}) will be
                                            {{ $lease->deposit_collected ? 'excluded — already collected' : 'added automatically to total' }}.
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes / Internal Memo</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"
                                placeholder="Add internal notes (optional)">{{ $invoice->notes }}</textarea>
                        </div>

                        @if ($invoice->type === 'ITEM_SALE')
                            {{-- Line Items Editor --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Line Items</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="editItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:28%">Item</th>
                                                <th style="width:28%">Description</th>
                                                <th style="width:12%" class="text-center">Qty</th>
                                                <th style="width:14%" class="text-center">Rate ($)</th>
                                                <th style="width:12%" class="text-center">Amount</th>
                                                <th style="width:6%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="editItemsBody">
                                            @foreach ($invoice->items as $idx => $invItem)
                                                <tr class="edit-item-row">
                                                    <td>
                                                        <select class="form-select form-select-sm edit-item-select"
                                                            name="items[{{ $idx }}][item_id]">
                                                            <option value="">Custom / Manual</option>
                                                            @foreach ($availableItems as $avItem)
                                                                <option value="{{ $avItem->id }}"
                                                                    data-name="{{ $avItem->name }}"
                                                                    data-price="{{ $avItem->price }}"
                                                                    {{ $invItem->item_id == $avItem->id ? 'selected' : '' }}>
                                                                    {{ $avItem->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" class="edit-item-name"
                                                            name="items[{{ $idx }}][item_name]"
                                                            value="{{ $invItem->item_name }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="items[{{ $idx }}][description]"
                                                            value="{{ $invItem->description }}"
                                                            placeholder="Description">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-center edit-item-qty"
                                                            name="items[{{ $idx }}][quantity]"
                                                            value="{{ $invItem->quantity }}" min="1" required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            class="form-control form-control-sm text-end edit-item-rate"
                                                            name="items[{{ $idx }}][rate]"
                                                            value="{{ $invItem->rate }}" min="0" step="0.01"
                                                            required>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span
                                                            class="edit-item-amount fw-bold">${{ number_format($invItem->amount, 2) }}</span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-edit-row"
                                                            title="Remove">
                                                            <i class="fe fe-trash-2"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">New Total:</td>
                                                <td class="text-center fw-bold text-primary" id="editItemsTotal">
                                                    ${{ number_format($invoice->total_amount, 2) }}
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="addEditItemBtn">
                                    <i class="fe fe-plus me-1"></i> Add Line Item
                                </button>
                            </div>
                        @endif

                        {{-- Live summary for non ITEM_SALE --}}
                        @if ($invoice->type !== 'ITEM_SALE')
                            <div class="bg-light border rounded-1 p-3 mb-0" id="editAmountSummary">
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Base Amount:</span>
                                    <strong class="text-dark"
                                        id="summaryBase">${{ number_format($invoice->amount, 2) }}</strong>
                                </div>
                                @if ($invoice->is_first_invoice && $invoice->includes_deposit && !$lease->deposit_collected)
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Security Deposit (auto-added):</span>
                                        <strong
                                            class="text-dark">${{ number_format($lease->deposit_amount ?? 0, 2) }}</strong>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Already Paid:</span>
                                    <strong
                                        class="text-success">${{ number_format($invoice->paid_amount ?? 0, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2">
                                    <span class="text-dark">New Balance Due:</span>
                                    <strong class="text-primary" id="summaryBalance">$0.00</strong>
                                </div>
                            </div>
                        @endif

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary d-inline-flex align-items-center"
                        data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning d-inline-flex align-items-center" id="saveEditBtn"
                        onclick="submitEditInvoice()">
                        <i class="fe fe-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- VOID / CANCEL INVOICE MODAL --}}
    <div class="modal fade" id="voidInvoiceModal" tabindex="-1" aria-labelledby="voidInvoiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="voidInvoiceModalLabel">
                        <i class="fe fe-alert-triangle me-2"></i>Void / Cancel Invoice
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong><i class="fe fe-alert-circle me-1"></i>Warning:</strong>
                        Voiding an invoice is <strong>permanent</strong>. No further payments will be accepted.
                        An audit record will be created for accounting history.
                    </div>

                    @if (($invoice->paid_amount ?? 0) > 0)
                        <div class="alert alert-danger">
                            <strong><i class="fe fe-dollar-sign me-1"></i>Payments Exist!</strong>
                            This invoice has <strong>${{ number_format($invoice->paid_amount, 2) }}</strong> in recorded
                            payments.
                            Voiding will <strong>not</strong> automatically refund these payments.
                            A reversal record will be created in the audit trail — please process any refunds manually.
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Reason for Voiding <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="voidReason" rows="3" maxlength="500"
                            placeholder="Enter the reason this invoice is being voided (required)..."></textarea>
                        <div class="form-text">This reason will be stored in the audit trail, max 500 characters.</div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Invoice: <strong>{{ $invoice->invoice_number }}</strong></span>
                            <span>Total: <strong>${{ number_format($invoice->total_amount, 2) }}</strong></span>
                            <span>Status: <strong>{{ $invoice->status }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary d-flex align-items-center" data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>
                        <span>Cancel</span>
                    </button>

                    <button type="button" class="btn btn-danger d-flex align-items-center" id="confirmVoidBtn"
                        onclick="submitVoidInvoice()">
                        <i class="fe fe-slash me-2"></i>
                        <span>Confirm Void Invoice</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- VOID PAID CASH INVOICE MODAL --}}
    <div class="modal fade" id="cancelPaidCashModal" tabindex="-1" aria-labelledby="cancelPaidCashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelPaidCashModalLabel">
                        <i class="fe fe-alert-triangle me-2"></i>Void Paid Cash Invoice
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger bg-danger-transparent text-danger mb-4">
                        <h6 class="fw-bold mb-2"><i class="fe fe-alert-circle me-2"></i>CRITICAL ACCOUNTING WARNING</h6>
                        <p class="small mb-0">You are about to <strong>permanently void</strong> a fully/partially paid cash invoice. This action is <strong>irreversible</strong> and will alter the lease's financial ledger.</p>
                    </div>

                    <h6 class="fw-semibold text-dark mb-2"><i class="fe fe-list me-2"></i>Post-Void Accounting Impacts:</h6>
                    <ul class="text-muted small ps-3 mb-4" style="list-style-type: square; line-height: 1.6;">
                        <li>The invoice status will be permanently changed to <strong>CANCELLED</strong>.</li>
                        <li>No further payments can be accepted for this invoice under any circumstances.</li>
                        <li>Invoice balances (Total Amount Paid and Balance Due) will be instantly zeroed out (<strong>$0.00</strong>).</li>
                        <li>Existing cash payment records will be marked as <strong>voided</strong> and excluded from payment history.</li>
                        <li><strong>Standard Double-Entry Reversal adjustments</strong> (Credit Adjustment for original debit charge, Debit Adjustment for mistaken payment credit) will be posted to the general ledger.</li>
                        <li>Voided cash collections will be <strong>fully excluded</strong> from all daily collection and rent reports, adjusting historical income tallies.</li>
                    </ul>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Reason for Voiding <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="cancelPaidCashReason" rows="3" maxlength="500"
                            placeholder="Provide a detailed explanation for this invoice void (e.g. entry error, double post)..." required></textarea>
                        <div class="form-text">This audit reason will be permanently saved in the General Ledger metadata.</div>
                    </div>

                    <div class="mb-0 pt-2 border-top">
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Invoice: <strong>{{ $invoice->invoice_number }}</strong></span>
                            <span>Paid Amount: <strong>${{ number_format($invoice->paid_amount ?? 0, 2) }}</strong></span>
                            <span>Status: <strong>{{ $invoice->status }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary d-flex align-items-center justify-content-center"
                        data-bs-dismiss="modal">
                        <i class="fe fe-x me-1"></i>
                        <span>Cancel</span>
                    </button>

                    <button type="button" class="btn btn-danger d-flex align-items-center justify-content-center"
                        id="confirmCancelPaidCashBtn" onclick="submitCancelPaidCashInvoice()">
                        <i class="fe fe-slash me-2"></i>
                        <span>Confirm Permanent Void</span>
                    </button>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('backend/plugins/bootstrap-datepicker/js/datepicker.js') }}"></script>
    <script>
        $('.datepicker2').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true,
            todayHighlight: true,
            width: 300
        });
        const balanceDue = {{ $balanceDue }};

        function showPaymentForm() {
            document.getElementById('paymentSection').style.display = 'block';
            document.querySelector('.col-lg-7').classList.remove('col-lg-7');
            document.querySelector('.invoice-container').classList.add('col-lg-7');
            updateRemainingBalance();
        }

        function hidePaymentForm() {
            document.getElementById('paymentSection').style.display = 'none';
            document.getElementById('paymentForm').reset();
            document.getElementById('paymentAmount').value = balanceDue;
        }

        function setPaymentAmount(amount) {
            document.getElementById('paymentAmount').value = amount.toFixed(2);
            updateRemainingBalance();
        }

        function updateRemainingBalance() {
            const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
            const remaining = balanceDue - paymentAmount;
            document.getElementById('remainingBalance').textContent = '$' + remaining.toFixed(2);

            if (remaining === 0) {
                document.getElementById('remainingBalance').classList.remove('text-primary');
                document.getElementById('remainingBalance').classList.add('text-success');
            } else {
                document.getElementById('remainingBalance').classList.remove('text-success');
                document.getElementById('remainingBalance').classList.add('text-primary');
            }
        }

        function submitPayment(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const paymentAmount = parseFloat(formData.get('amount'));
            console.log(formData);

            if (paymentAmount <= 0 || paymentAmount > balanceDue) {
                toastr.error('Invalid payment amount');
                return;
            }

            Swal.fire({
                title: 'Confirm Payment',
                text: 'Are you sure you want to record a payment of $' + paymentAmount.toFixed(2) + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, record it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('invoices.payments.store', $invoice->id) }}`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message || 'Payment recorded successfully');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                toastr.error(response.message || 'Failed to record payment');
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON?.message || 'Failed to record payment';
                            toastr.error(message);
                        }
                    });
                }
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateRemainingBalance();
            initEditModal();
        });

        // ═══════════════════════════════════════════════════════════
        // EDIT INVOICE
        // ═══════════════════════════════════════════════════════════

        function showEditForm() {
            $('#editInvoiceModal').modal('show');
        }

        function initEditModal() {
            // Live balance recalculation for non-ITEM_SALE invoices
            const amountInput = document.getElementById('edit_amount');
            if (amountInput) {
                amountInput.addEventListener('input', recalcEditSummary);
                recalcEditSummary(); // initial
            }

            // Line item events (ITEM_SALE)
            bindEditItemEvents();

            // Add item row button
            const addBtn = document.getElementById('addEditItemBtn');
            if (addBtn) {
                addBtn.addEventListener('click', addEditItemRow);
            }
        }

        function recalcEditSummary() {
            const amountInput = document.getElementById('edit_amount');
            if (!amountInput) return;

            const base = parseFloat(amountInput.value) || 0;
            const deposit =
                {{ $invoice->is_first_invoice && $invoice->includes_deposit && !$lease->deposit_collected ? $lease->deposit_amount ?? 0 : 0 }};
            const paid = {{ $invoice->paid_amount ?? 0 }};
            const newTotal = base + deposit;
            const newBalance = Math.max(0, newTotal - paid);

            const summaryBase = document.getElementById('summaryBase');
            const summaryBalance = document.getElementById('summaryBalance');
            if (summaryBase) summaryBase.textContent = '$' + base.toFixed(2);
            if (summaryBalance) summaryBalance.textContent = '$' + newBalance.toFixed(2);
        }

        // ── ITEM_SALE line items ─────────────────────────────────────

        function bindEditItemEvents() {
            document.querySelectorAll('#editItemsBody .edit-item-qty, #editItemsBody .edit-item-rate').forEach(input => {
                input.addEventListener('input', recalcEditItems);
            });
            document.querySelectorAll('#editItemsBody .edit-item-select').forEach(sel => {
                sel.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const opt = this.options[this.selectedIndex];
                    const name = opt.dataset.name || '';
                    const price = parseFloat(opt.dataset.price) || 0;
                    row.querySelector('.edit-item-name').value = name;
                    if (price > 0) row.querySelector('.edit-item-rate').value = price.toFixed(2);
                    recalcEditItems();
                });
            });
            document.querySelectorAll('#editItemsBody .remove-edit-row').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (document.querySelectorAll('#editItemsBody .edit-item-row').length <= 1) {
                        toastr.warning('At least one line item is required.');
                        return;
                    }
                    this.closest('tr').remove();
                    reindexEditRows();
                    recalcEditItems();
                });
            });
        }

        function addEditItemRow() {
            const tbody = document.getElementById('editItemsBody');
            const idx = tbody.querySelectorAll('.edit-item-row').length;
            const availableItems = @json($availableItems->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'price' => $i->price]));

            let opts = '<option value="">Custom / Manual</option>';
            availableItems.forEach(i => {
                opts += `<option value="${i.id}" data-name="${i.name}" data-price="${i.price}">${i.name}</option>`;
            });

            const row = document.createElement('tr');
            row.className = 'edit-item-row';
            row.innerHTML = `
            <td>
                <select class="form-select form-select-sm edit-item-select" name="items[${idx}][item_id]">${opts}</select>
                <input type="hidden" class="edit-item-name" name="items[${idx}][item_name]" value="Custom">
            </td>
            <td><input type="text" class="form-control form-control-sm" name="items[${idx}][description]" placeholder="Description"></td>
            <td><input type="number" class="form-control form-control-sm text-center edit-item-qty" name="items[${idx}][quantity]" value="1" min="1" required></td>
            <td><input type="number" class="form-control form-control-sm text-end edit-item-rate" name="items[${idx}][rate]" value="0" min="0" step="0.01" required></td>
            <td class="text-center align-middle"><span class="edit-item-amount fw-bold">$0.00</span></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger remove-edit-row" title="Remove">
                    <i class="fe fe-trash-2"></i>
                </button>
            </td>`;
            tbody.appendChild(row);
            bindEditItemEvents();
        }

        function reindexEditRows() {
            document.querySelectorAll('#editItemsBody .edit-item-row').forEach((row, i) => {
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/items\[\d+\]/, `items[${i}]`);
                });
            });
        }

        function recalcEditItems() {
            let total = 0;
            document.querySelectorAll('#editItemsBody .edit-item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.edit-item-qty').value) || 0;
                const rate = parseFloat(row.querySelector('.edit-item-rate').value) || 0;
                const amt = qty * rate;
                total += amt;
                row.querySelector('.edit-item-amount').textContent = '$' + amt.toFixed(2);
            });
            const totalEl = document.getElementById('editItemsTotal');
            if (totalEl) totalEl.textContent = '$' + total.toFixed(2);
        }

        function submitEditInvoice() {
            const form = document.getElementById('editInvoiceForm');
            const dueDate = document.getElementById('edit_due_date').value;

            if (!dueDate) {
                toastr.error('Due date is required.');
                return;
            }

            // Validate line-items if ITEM_SALE
            @if ($invoice->type === 'ITEM_SALE')
                const rows = document.querySelectorAll('#editItemsBody .edit-item-row');
                if (rows.length === 0) {
                    toastr.error('At least one line item is required.');
                    return;
                }
                let itemsValid = true;
                rows.forEach(row => {
                    const name = row.querySelector('.edit-item-name').value;
                    const qty = parseFloat(row.querySelector('.edit-item-qty').value);
                    const rate = parseFloat(row.querySelector('.edit-item-rate').value);
                    if (!name || qty < 1 || rate < 0) itemsValid = false;
                });
                if (!itemsValid) {
                    toastr.error('Please fill all line item fields correctly.');
                    return;
                }
            @endif

            const saveBtn = document.getElementById('saveEditBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

            const formData = new FormData(form);
            // Laravel PUT requires _method spoofing via FormData for multipart
            formData.append('_method', 'PUT');

            $.ajax({
                url: '{{ route('invoices.update', $invoice->id) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message || 'Invoice updated successfully.');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        toastr.error(res.message || 'Failed to update invoice.');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fe fe-save me-1"></i>Save Changes';
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to update invoice.';
                    toastr.error(msg);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fe fe-save me-1"></i>Save Changes';
                }
            });
        }

        // ═══════════════════════════════════════════════════════════
        // VOID INVOICE
        // ═══════════════════════════════════════════════════════════

        function showVoidModal() {
            document.getElementById('voidReason').value = '';
            $('#voidInvoiceModal').modal('show');
        }

        function submitVoidInvoice() {
            const reason = document.getElementById('voidReason').value.trim();
            if (!reason) {
                toastr.error('A reason is required to void this invoice.');
                document.getElementById('voidReason').focus();
                return;
            }

            const confirmBtn = document.getElementById('confirmVoidBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

            $.ajax({
                url: '{{ route('invoices.cancel', $invoice->id) }}',
                type: 'POST',
                data: {
                    reason: reason,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message || 'Invoice voided successfully.');
                        $('#voidInvoiceModal').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(res.message || 'Failed to void invoice.');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fe fe-slash me-2"></i>Confirm Void Invoice';
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to void invoice.';
                    toastr.error(msg);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fe fe-slash me-2"></i>Confirm Void Invoice';
                }
            });
        }

        // ═══════════════════════════════════════════════════════════
        // CANCEL PAID CASH INVOICE
        // ═══════════════════════════════════════════════════════════

        function showCancelPaidCashModal() {
            document.getElementById('cancelPaidCashReason').value = '';
            $('#cancelPaidCashModal').modal('show');
        }

        function submitCancelPaidCashInvoice() {
            const reason = document.getElementById('cancelPaidCashReason').value.trim();
            if (!reason) {
                toastr.error('A reason is required to void this paid cash invoice.');
                document.getElementById('cancelPaidCashReason').focus();
                return;
            }

            const confirmBtn = document.getElementById('confirmCancelPaidCashBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';

            $.ajax({
                url: '{{ route('invoices.cancel.paid.cash', $invoice->id) }}',
                type: 'POST',
                data: {
                    reason: reason,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message || 'Paid cash invoice voided successfully.');
                        $('#cancelPaidCashModal').modal('hide');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(res.message || 'Failed to void paid cash invoice.');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fe fe-slash me-2"></i>Confirm Permanent Void';
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to void paid cash invoice.';
                    toastr.error(msg);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fe fe-slash me-2"></i>Confirm Permanent Void';
                }
            });
        }
    </script>
@endpush

@push('styles')
    <style>
        .invoice-container {
            /* max-width: 900px; */
            margin: 0 auto;
            padding: 0;
        }

        .invoice-paper {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        /* Invoice Header */
        .invoice-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .invoice-brand h1 {
            color: #fff;
            font-size: 36px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 3px;
        }

        .invoice-number {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            display: block;
            margin-top: 5px;
        }

        .status-badge {
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
        }

        .status-badge.paid {
            background: rgba(76, 175, 80, 0.2);
            color: #81c784;
        }

        .status-badge.unpaid {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd54f;
        }

        .status-badge.overdue {
            background: rgba(244, 67, 54, 0.2);
            color: #ef9a9a;
        }

        .status-badge.cancelled {
            background: rgba(108, 117, 125, 0.2);
            color: #adb5bd;
            text-decoration: line-through;
        }

        /* Voided invoice banner */
        .voided-banner {
            background: #fff3cd;
            border: 2px dashed #dc3545;
            color: #842029;
            padding: 14px 30px;
            font-size: 15px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        /* Invoice Info Grid */
        .invoice-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            padding: 40px;
            background: #f8fafc;
            border-bottom: 1px solid #e9ecef;
        }

        .info-block label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 8px;
        }

        .info-block h5 {
            font-weight: 600;
            color: #1e3a5f;
            margin: 0 0 5px;
            font-size: 18px;
        }

        .date-row {
            margin-bottom: 12px;
        }

        .date-row:last-child {
            margin-bottom: 0;
        }

        .date-row label {
            margin-bottom: 4px;
        }

        .date-row span {
            display: block;
            font-weight: 600;
            color: #1e3a5f;
            font-size: 15px;
        }

        /* Lease Info Banner */
        .lease-info-banner {
            display: flex;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
        }

        .banner-item {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 30px;
            border-right: 1px solid #e9ecef;
        }

        .banner-item:last-child {
            border-right: none;
        }

        .banner-item i {
            font-size: 24px;
            color: #2d5a87;
            opacity: 0.7;
        }

        .banner-item label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 2px;
        }

        .banner-item span {
            font-weight: 600;
            color: #1e3a5f;
            font-size: 14px;
        }

        /* Invoice Items Table */
        .invoice-items {
            padding: 0 40px;
        }

        .invoice-items table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-items th {
            padding: 16px 20px;
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }

        .invoice-items td {
            padding: 24px 20px;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: top;
        }

        .invoice-items .item-desc {
            width: 50%;
        }

        .invoice-items .item-qty,
        .invoice-items .item-rate {
            text-align: center;
            width: 15%;
        }

        .invoice-items .item-amount {
            text-align: right;
            width: 20%;
            font-weight: 600;
            color: #1e3a5f;
        }

        .invoice-items td strong {
            color: #1e3a5f;
            font-size: 15px;
        }

        .deposit-row {
            background: #fffbeb;
        }

        .deposit-row td {
            border-bottom: 2px solid #fcd34d;
        }

        /* Invoice Summary */
        .invoice-summary {
            padding: 30px 40px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-details {
            width: 350px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            color: #6c757d;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.deposit {
            background: #fffbeb;
            margin: 0 -25px;
            padding: 12px 25px;
            border-bottom: 1px solid #fcd34d;
        }

        .summary-row.deposit.collected {
            background: #f0fdf4;
            border-bottom: 1px solid #86efac;
        }

        .summary-row.total {
            border-top: 2px solid #1e3a5f;
            margin-top: 10px;
            padding-top: 20px;
            font-size: 18px;
            font-weight: 700;
            color: #1e3a5f;
        }

        .summary-row.paid-badge,
        .summary-row.balance {
            border-bottom: none;
            padding: 8px 0;
        }

        /* Payment Info */
        .payment-info {
            margin: 0 40px 30px;
            padding: 20px 25px;
            background: #fff8e1;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
        }

        .payment-info h6 {
            color: #f57c00;
            margin: 0 0 10px;
            font-weight: 600;
        }

        .payment-info.paid {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }

        .payment-info.paid h6 {
            color: #388e3c;
        }

        .payment-history {
            padding: 0 40px 20px;
        }

        /* Invoice Footer */
        .invoice-footer {
            text-align: center;
            padding: 30px 40px;
            background: #fff8f8b0;
            border-top: 1px solid #e9ecef;
        }

        .invoice-footer p {
            margin: 0;
            color: #1e3a5f;
            font-weight: 600;
            font-size: 16px;
        }

        .bg-warning-light {
            background: rgba(255, 193, 7, 0.15) !important;
        }

        .bg-success-light {
            background: rgba(76, 175, 80, 0.15) !important;
        }

        /* Print Styles */
        @media print {

            .page-header,
            .app-sidebar,
            .app-header {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .invoice-container {
                max-width: 100%;
                padding: 0;
            }

            .invoice-paper {
                box-shadow: none;
                border-radius: 0;
            }
        }

        /* Responsive */
        @media (max-width: 767px) {
            .invoice-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 30px 20px;
            }

            .invoice-info-grid {
                grid-template-columns: 1fr;
                padding: 30px 20px;
            }

            .info-block.text-end {
                text-align: left !important;
            }

            .lease-info-banner {
                flex-direction: column;
            }

            .banner-item {
                border-right: none;
                border-bottom: 1px solid #e9ecef;
            }

            .banner-item:last-child {
                border-bottom: none;
            }

            .invoice-items {
                padding: 0 20px;
                overflow-x: auto;
            }

            .invoice-summary {
                padding: 20px;
            }

            .summary-details {
                width: 100%;
            }

            .payment-info {
                margin: 0 20px 20px;
            }

            .invoice-footer {
                padding: 20px;
            }
        }
    </style>
@endpush
