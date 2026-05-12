<?php

namespace App\Http\Controllers\Web\Backend\Income;

use Exception;
use App\Models\Item;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class IncomeController extends Controller
{
    /**
     * Display invoice listing page
     */
    public function index(Request $request)
    {
        // Calculate all invoice statistics accurately
        $unpaidAmount = Invoice::where('status', 'UNPAID')->sum('balance_due') ?? 0;
        $overdueAmount = Invoice::overdue()->sum('balance_due') ?? 0;
        $partialAmount = Invoice::where('status', 'PARTIAL')->sum('balance_due') ?? 0;
        $paidAmount = Invoice::where('status', 'PAID')->sum('paid_amount') ?? 0;
        $processingAmount = Invoice::where('status', 'PROCESSING')->sum('balance_due') ?? 0;

        // Total invoice amount = sum of all total_amount regardless of status
        $totalInvoiceAmount = Invoice::sum('total_amount') ?? 0;

        $stats = [
            'total' => $totalInvoiceAmount,  // Sum of all invoice total_amount
            'unpaid' => $unpaidAmount,       // Sum of balance_due for UNPAID status
            'overdue' => $overdueAmount,     // Sum of balance_due for overdue invoices
            'partial' => $partialAmount,     // Sum of balance_due for PARTIAL status
            'paid' => $paidAmount,           // Sum of paid_amount for PAID status
            'processing' => $processingAmount, // Sum of balance_due for PROCESSING status
            'invoice_count' => Invoice::count(), // Total number of invoices
            'due_amount' => $unpaidAmount + $overdueAmount + $partialAmount, // Total amount due
            'collected_amount' => $paidAmount, // Total collected amount
        ];

        $properties = Property::where('is_active', true)->get();
        $tenants = Tenant::with('profile')->get();

        return view('backend.layouts.income.index', compact('stats', 'properties', 'tenants'));
    }

    /**
     * Get invoice data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Invoice::query()
                ->select([
                    'invoices.id',
                    'invoices.invoice_number',
                    'invoices.tenant_id',
                    'invoices.lease_id',
                    'invoices.type',
                    'invoices.status',
                    'invoices.due_date',
                    'invoices.total_amount',
                    'invoices.paid_amount',
                    'invoices.balance_due',
                    'invoices.is_recurring',
                    'invoices.recurring_frequency',
                    'invoices.created_at'
                ])
                ->with([
                    'tenant:id,email' => ['profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar'],
                    'items:id,invoice_id,item_name,quantity,rate,amount',
                    'lease.property:id,name'
                ])
                ->orderBy('invoices.id', 'desc');

            // Filters
            if ($request->filled('property_id')) {
                $propertyIds = is_array($request->property_id)
                    ? $request->property_id
                    : explode(',', $request->property_id);

                $query->whereHas('lease.property', function ($q) use ($propertyIds) {
                    $q->whereIn('id', $propertyIds);
                });
            }

            if ($request->filled('tenant_id')) {
                $query->where('invoices.tenant_id', $request->tenant_id);
            }

            if ($request->filled('status')) {
                $query->where('invoices.status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('invoices.type', $request->type);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('invoices.due_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('invoices.due_date', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('tenant', function ($query, $keyword) {
                    $query->whereHas('tenant.profile', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('property', function ($query, $keyword) {
                    $query->whereHas('lease.property', function ($q) use ($keyword) {
                        $q->where('property.name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('invoice_info', function ($data) {
                    $recurring = $data->is_recurring
                        ? '<span class="badge bg-info-transparent ms-1"><i class="fe fe-repeat me-1"></i>' . ucfirst(strtolower($data->recurring_frequency ?? 'Monthly')) . '</span>'
                        : '';

                    return '<div>
                                <div class="fw-semibold">' . e($data->invoice_number) . $recurring . '</div>
                                <small class="text-muted">' . ucfirst(str_replace('_', ' ', strtolower($data->type))) . '</small>
                            </div>';
                })
                ->addColumn('tenant_info', function ($data) {

                    if (!$data->tenant) {
                        return '<span class="text-muted">No Tenant</span>';
                    }

                    $profile = $data->tenant->profile;

                    $fullName = $profile
                        ? trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                        : '';

                    // fallback to email if name empty
                    $displayName = $fullName ?: $data->tenant->email;

                    $avatar = ($profile && $profile->avatar)
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                    <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                    <div>
                        <div>' . e($displayName) . '</div>
                        ' . ($fullName ? '<small class="text-muted">' . e($data->tenant->email) . '</small>' : '') . '
                    </div>
                 </div>';
                })
                ->addColumn('property_info', function ($data) {
                    if (!$data->lease->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    return '<div>
                                <div class="fw-semibold">' . e($data->lease->property->name) . '</div>
                            </div>';
                })
                ->addColumn('amount_info', function ($data) {
                    $progressPercent = $data->total_amount > 0
                        ? round(($data->paid_amount / $data->total_amount) * 100)
                        : 0;

                    return '<div>
                                <div class="fw-semibold">$' . number_format($data->total_amount, 2) . '</div>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: ' . $progressPercent . '%"></div>
                                </div>
                                <small class="text-muted">Paid: $' . number_format($data->paid_amount, 2) . ' | Due: $' . number_format($data->balance_due, 2) . '</small>
                            </div>';
                })
                ->addColumn('due_date', function ($data) {
                    $dueDate = date('M d, Y', strtotime($data->due_date));
                    $isOverdue = $data->isOverdue();
                    $color = $isOverdue ? 'text-danger' : 'text-muted';

                    return '<span class="' . $color . '">' . $dueDate . '</span>';
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'UNPAID' => 'warning',
                        'PARTIAL' => 'info',
                        'PAID' => 'success',
                        'OVERDUE' => 'danger',
                        'CANCELLED' => 'secondary'
                    ];

                    $status = $data->status;
                    if ($data->isOverdue() && $status !== 'PAID') {
                        $status = 'OVERDUE';
                    }

                    $color = $statusColors[$status] ?? 'secondary';
                    $label = $status === 'CANCELLED' ? 'Voided' : ucfirst(strtolower($status));

                    return '<span class="badge p-3 bg-' . $color . '">' . $label . '</span>';
                })
                ->rawColumns(['invoice_info', 'tenant_info', 'property_info', 'amount_info', 'due_date', 'status_badge'])
                ->make(true);
        }
    }

    /**
     * Show create invoice form
     */
    public function create()
    {
        $tenants = Tenant::with('profile')
            ->whereHas('leases', function ($q) {
                $q->where('status', 'ACTIVE');
            })
            ->get();
        $items = Item::where('status', true)->get();
        // return $tenants;exit();

        return view('backend.layouts.income.create-invoice', compact('tenants', 'items'));
    }

    /**
     * Store new invoice
     */
    public function store(Request $request)
    {
        $rules = [
            'tenant_id' => 'required|exists:tenants,id',
            'due_date' => 'required|date',
            'type' => 'required|in:DEPOSIT,RENT,FEE,ITEM_SALE,OTHER',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:WEEKLY,MONTHLY,YEARLY,CUSTOM',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.item_name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'custom_recurring' => 'nullable|array',
            'custom_recurring.*.due_date' => 'required_with:custom_recurring|date',
            'custom_recurring.*.amount' => 'required_with:custom_recurring|numeric|min:0.01',
            'custom_recurring.*.description' => 'nullable|string',
        ];

        $request->validate($rules);

        DB::beginTransaction();

        try {
            $tenant = Tenant::with('leases.property')->find($request->tenant_id);
            $activeLease = Lease::where('tenant_id', $request->tenant_id)
                ->where('status', 'ACTIVE')
                ->first();

            $leaseId = $activeLease?->id;
            $propertyId = $tenant->leases->first()->property_id ?? null;

            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['rate'];
            }

            // Generate invoice number
            $invoiceNumber = Invoice::where('lease_id', $leaseId)->where('tenant_id', $request->tenant_id)->count() + 1;
            // $invoiceNumber = $this->generateInvoiceNumber();
            $invoiceNumber = 'INV-' . $leaseId . '-' . $request->tenant_id . '-' . str_pad($invoiceNumber, 3, '0', STR_PAD_LEFT);

            // Create main invoice
            $invoice = Invoice::create([
                'tenant_id' => $request->tenant_id,
                'lease_id' => $leaseId,
                'invoice_number' => $invoiceNumber,
                'amount' => $totalAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount,
                'issue_date' => now(),
                'due_date' => Carbon::parse($request->due_date)->format('Y-m-d'),
                'type' => 'ITEM_SALE',
                'status' => 'UNPAID',
                'is_recurring' => $request->boolean('is_recurring'),
                'recurring_frequency' => $request->is_recurring ? $request->recurring_frequency : null,
                'notes' => $request->notes,
            ]);

            // Create invoice items
            foreach ($request->items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'rate' => $itemData['rate'],
                    'amount' => $itemData['quantity'] * $itemData['rate'],
                ]);
            }

            // Handle custom recurring invoices
            if ($request->boolean('is_recurring') && $request->recurring_frequency === 'CUSTOM' && $request->filled('custom_recurring')) {
                $this->createCustomRecurringInvoices($request, $invoice, $tenant, $leaseId);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully!',
                'invoice_id' => $invoice->id,
                'redirect_url' => route('invoices.show', $invoice->id),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create custom recurring invoices
     */
    private function createCustomRecurringInvoices($request, $mainInvoice, $tenant, $leaseId)
    {
        foreach ($request->custom_recurring as $customPayment) {
            // Generate invoice number
            $invoiceNumber = Invoice::where('lease_id', $leaseId)->where('tenant_id', $request->tenant_id)->count() + 1;
            // $invoiceNumber = $this->generateInvoiceNumber();
            $customInvoiceNumber = 'INV-' . $leaseId . '-' . $request->tenant_id . '-' . str_pad($invoiceNumber, 3, '0', STR_PAD_LEFT);
            // $customInvoiceNumber = $invoiceNumber;
            $customAmount = $customPayment['amount'];

            $customInvoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'lease_id' => $leaseId,
                'invoice_number' => $customInvoiceNumber,
                'amount' => $customAmount,
                'total_amount' => $customAmount,
                'balance_due' => $customAmount,
                'issue_date' => now(),
                'due_date' => $customPayment['due_date'],
                'type' => $request->type,
                'status' => 'UNPAID',
                'is_recurring' => true,
                'recurring_frequency' => 'CUSTOM',
                'notes' => $customPayment['description'] ?? 'Custom recurring payment',
                'metadata' => json_encode(['parent_invoice_id' => $mainInvoice->id]),
            ]);

            // Create invoice items for custom recurring
            foreach ($request->items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $customInvoice->id,
                    'item_id' => $itemData['item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'rate' => $customAmount / $itemData['quantity'], // Adjust rate based on custom amount
                    'amount' => $customAmount,
                ]);
            }
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'INV-' . $year . $month;

        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Show invoice details
     */
    public function show($id)
    {
        $invoice = Invoice::with([
            'tenant.profile',
            'property',
            'items.item',
            'lease'
        ])->findOrFail($id);

        return view('backend.layouts.invoices.show', compact('invoice'));
    }

    /**
     * Get tenant info for invoice creation
     */
    public function getTenantInfo($tenantId)
    {
        try {
            $tenant = Tenant::with([
                'profile',
                'leases' => function ($q) {
                    $q->where('status', 'ACTIVE')->with('property');
                }
            ])->findOrFail($tenantId);

            $profile = $tenant->profile;
            $activeLease = $tenant->leases->first();

            return response()->json([
                'success' => true,
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')),
                    'email' => $tenant->email,
                    'phone' => $profile->phone ?? 'N/A',
                    'property' => $activeLease ? $activeLease->property->name : 'N/A',
                    'property_id' => $activeLease ? $activeLease->property_id : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
    }
}
