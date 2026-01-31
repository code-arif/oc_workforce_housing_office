@extends('backend.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h1 class="page-title">Invoice {{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('leases.index') }}">Leases</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('leases.show', $invoice->lease_id) }}">Lease Details</a></li>
                                    <li class="breadcrumb-item active">Invoice</li>
                                </ol>
                            </nav>
                        </div>

                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-success">
                            <i class="fe fe-arrow-left me-2"></i>Back to invoices
                        </a>
                        <a href="{{ route('leases.show', $invoice->lease_id) }}" class="btn btn-outline-secondary">
                            <i class="fe fe-arrow-left me-2"></i>Back to Lease
                        </a>
                        <a href="{{ route('invoices.download.pdf', $invoice->id) }}" class="btn btn-outline-primary">
                            <i class="fe fe-download me-2"></i>Download PDF
                        </a>
                        @if($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED' && $canMakePayment)
                        <button class="btn btn-success" onclick="showPaymentForm()">
                            <i class="fe fe-dollar-sign me-2"></i>Make Payment
                        </button>
                        @elseif($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED' && !$canMakePayment)
                        <button class="btn btn-secondary" disabled title="Previous invoice must be paid first">
                            <i class="fe fe-lock me-2"></i>Pay Previous Invoice First
                        </button>
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
                                    <span class="invoice-number">{{ $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="invoice-status">
                                    @if($invoice->status == 'PAID')
                                        <span class="status-badge paid">
                                            <i class="fe fe-check-circle me-1"></i> Paid
                                        </span>
                                    @elseif($invoice->status == 'PARTIAL')
                                        <span class="status-badge paid">
                                            <i class="fe fe-credit-card me-1"></i> Partially Paid
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
                                    @if($lease->property)
                                    <p class="text-muted small mb-0">{{ $lease->property->address }}</p>
                                    @endif
                                </div>
                                <div class="info-block">
                                    <label>Bill To</label>
                                    @php
                                        $profile = $lease->tenant ? $lease->tenant->profile : null;
                                        $tenantName = $profile ? trim($profile->first_name . ' ' . ($profile->last_name ?? '')) : 'N/A';
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
                                    @if($invoice->paid_at)
                                    <div class="date-row">
                                        <label>Paid On</label>
                                        <span class="text-success">{{ date('M d, Y', strtotime($invoice->paid_at)) }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

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
                                                    <strong>{{ str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))) }} Rent</strong>
                                                    <p class="text-muted small mb-0">Rent payment for {{ date('F Y', strtotime($invoice->due_date)) }}</p>
                                                </td>
                                                <td class="item-qty">1</td>
                                                <td class="item-rate">${{ number_format($invoice->amount, 2) }}</td>
                                                <td class="item-amount">${{ number_format($invoice->amount, 2) }}</td>
                                            </tr>

                                            <!-- Security Deposit (only for first invoice if includes deposit and not collected) -->
                                            @if($isFirstInvoice && $depositInvoice !== null)
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
                                            @endif
                                        @else
                                            @foreach ($invoice->items as $item)
                                                <tr>
                                                    <td class="item-desc">
                                                        <strong>{{ $item->item->name }}</strong>
                                                        @if($item->description)
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

                                    @if($isFirstInvoice && $depositInvoice !== null)
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
                                    @endif

                                    <div class="summary-row total">
                                        <span>Total Invoice Amount</span>
                                        <span>${{ number_format($totalDue, 2) }}</span>
                                    </div>

                                    @if($totalPaid > 0)
                                    <div class="summary-row paid-amount">
                                        <span>Amount Paid</span>
                                        <span class="text-success">${{ number_format($totalPaid, 2) }}</span>
                                    </div>
                                    @endif

                                    <div class="summary-row balance {{ $balanceDue == 0 ? 'text-success' : 'text-danger' }}">
                                        <span>Balance Due</span>
                                        <span class="fw-bold">${{ number_format($balanceDue, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment History -->
                            @if($invoice->payments && $invoice->payments->count() > 0)
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
                                            @foreach($invoice->payments as $payment)
                                            <tr>
                                                <td>{{ date('M d, Y', strtotime($payment->payment_date)) }}</td>
                                                <td class="text-success fw-bold">${{ number_format($payment->amount, 2) }}</td>
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
                            @if($invoice->status !== 'PAID')
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
                                    Thank you for your payment. This invoice has been paid in full on {{ date('M d, Y', strtotime($invoice->paid_at)) }}.
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
                                        @if($totalPaid > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Already Paid:</span>
                                            <strong class="text-success">${{ number_format($totalPaid, 2) }}</strong>
                                        </div>
                                        @endif
                                        <div class="d-flex justify-content-between">
                                            <span>Balance Due:</span>
                                            <strong class="text-danger" id="balanceDueAmount">${{ number_format($balanceDue, 2) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Payment Amount -->
                                    <div class="mb-3">
                                        <label for="paymentAmount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number"
                                                   class="form-control"
                                                   id="paymentAmount"
                                                   name="amount"
                                                   min="0"
                                                   max="{{ $balanceDue }}"
                                                   value="{{ $balanceDue }}"
                                                   required
                                                   oninput="updateRemainingBalance()">
                                        </div>
                                        <div class="form-text">
                                            Maximum: ${{ number_format($balanceDue, 2) }}
                                        </div>
                                    </div>

                                    <!-- Quick Amount Buttons -->
                                    <div class="mb-3">
                                        <label class="form-label">Quick Select</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @if($balanceDue >= 100)
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setPaymentAmount({{ min(100, $balanceDue) }})">
                                                $100
                                            </button>
                                            @endif
                                            @if($balanceDue >= 500)
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setPaymentAmount({{ min(500, $balanceDue) }})">
                                                $500
                                            </button>
                                            @endif
                                            @if($balanceDue > 0)
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setPaymentAmount({{ $balanceDue / 2 }})">
                                                50% (${{ number_format($balanceDue / 2, 2) }})
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="setPaymentAmount({{ $balanceDue }})">
                                                Full (${{ number_format($balanceDue, 2) }})
                                            </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Remaining Balance After Payment -->
                                    <div class="alert alert-light mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Remaining Balance:</span>
                                            <strong id="remainingBalance" class="text-primary">${{ number_format(0, 2) }}</strong>
                                        </div>
                                    </div>

                                    <!-- Payment Date -->
                                    <div class="mb-3">
                                        <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control datepicker2"
                                               id="paymentDate"
                                               name="payment_date"
                                               value="{{ date('Y-m-d') }}"
                                               max="{{ date('Y-m-d') }}"
                                               required>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="mb-3">
                                        <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                                            <option value="">Select payment method</option>
                                            <option value="cash">Cash</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <!-- Reference Number -->
                                    <div class="mb-3">
                                        <label for="referenceNumber" class="form-label">Reference/Transaction Number</label>
                                        <input type="text"
                                               class="form-control"
                                               id="referenceNumber"
                                               name="reference_number"
                                               placeholder="Optional">
                                    </div>

                                    <!-- Payment Note -->
                                    <div class="mb-4">
                                        <label for="paymentNote" class="form-label">Note</label>
                                        <textarea class="form-control"
                                                  id="paymentNote"
                                                  name="note"
                                                  rows="3"
                                                  placeholder="Add any additional notes (optional)"></textarea>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fe fe-check me-2"></i>Submit Payment
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="hidePaymentForm()">
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
@endsection

@push('scripts')
<script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
<script>

    $('.datepicker2').datepicker({
        format: 'yyyy-mm-dd',
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

        if (paymentAmount <= 0 || paymentAmount > balanceDue) {
            toastr.error('Invalid payment amount');
            return;
        }

        if (!confirm('Confirm payment of $' + paymentAmount.toFixed(2) + '?')) {
            return;
        }

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

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateRemainingBalance();
    });
</script>
@endpush

@push('styles')
<style>
    .invoice-container {
        /* max-width: 900px; */
        margin: 0 auto;
        padding: 20px 0;
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
        background: #f8fafc;
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
