<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use App\Http\Controllers\Controller;
use App\Mail\Tenant\LeaseSignatureRequestMail;
use App\Mail\Tenant\TenantPasswordRestLinkMail;
use App\Mail\TenantApplication\TenantWelcomeMail;
use App\Models\Bed;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Lease\LeaseDocument;
use App\Models\Lease\LeaseTemplate;
use App\Models\LeaseAssignment;
use App\Models\LeasePaymentSchedule;
use App\Models\Property;
use App\Models\Season;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class LeaseController extends Controller
{
    /**
     * lease index page
     */
    public function index(Request $request)
    {
        // Get statistics - OPTIMIZED: Uses index on status column
        $stats = [
            'active' => Lease::where('status', 'ACTIVE')->count(),
            'inProcess' => Lease::whereIn('status', ['PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])->count(),
            'future' => Lease::where('status', 'DRAFT')->where('start_date', '>', now())->count(),
            'expiring' => Lease::where('status', 'ACTIVE')
                ->whereBetween('end_date', [now(), now()->addDays(90)])
                ->count(),
            'expired' => Lease::whereIn('status', ['TERMINATED', 'COMPLETED'])->count()
        ];

        // OPTIMIZED: Select only needed columns for dropdown population
        $properties = Property::where('is_active', true)->select('id', 'name')->get();
        $tenants = Tenant::with('profile:id,tenant_id,first_name,last_name')
            ->select('id', 'email')
            ->limit(500) // Limit for dropdown performance
            ->get();

        return view('backend.layouts.leases.lease.index', compact('stats', 'properties', 'tenants'));
    }

    /**
     * Lease list
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Optimized query with eager loading and selective columns
            $query = Lease::query()
                ->select([
                    'leases.id',
                    'leases.tenant_id',
                    'leases.property_id',
                    'leases.status',
                    'leases.start_date',
                    'leases.end_date',
                    'leases.rent_amount',
                    'leases.deposit_amount',
                    'leases.payment_frequency',
                    'leases.created_at'
                ])
                ->with([
                    'tenant:id,email' => [
                        'profile:id,tenant_id,first_name,middle_name,last_name,phone,avatar'
                    ],
                    'property',
                    'assignments' => function ($q) {
                        $q->select('id', 'lease_id', 'bed_id', 'is_current')
                            ->where('is_current', true)
                            ->whereNull('deleted_at')
                            ->with('bed:id,bed_label,room_id');
                    },
                    'documents:id,lease_id,tenant_signed_at,admin_signed_at'
                ])
                ->orderBy('leases.id', 'desc');

            // Status filter
            if ($request->filled('status')) {
                $query->where('leases.status', $request->status);
            }

            // Property filter
            if ($request->filled('property_id')) {
                $query->where('leases.property_id', $request->property_id);
            }

            if($request->filled('bed_id')){
                $query->whereHas('assignments', function($q) use ($request){
                    $q->where('bed_id', $request->bed_id);
                });
            }

            // Date range filter
            if ($request->filled('tenant_id')) {
                $query->where('leases.tenant_id', $request->tenant_id);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('leases.end_date', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->filterColumn('tenant', function ($query, $keyword) {
                    $query->whereHas('tenant.profile', function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, ''))"), 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('property', function ($query, $keyword) {
                    $query->whereHas('property', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'DRAFT' => 'secondary',
                        'PENDING_TENANT_SIGN' => 'warning',
                        'PENDING_ADMIN_SIGN' => 'info',
                        'ACTIVE' => 'success',
                        'TERMINATED' => 'danger',
                        'COMPLETED' => 'dark'
                    ];

                    $statusLabels = [
                        'DRAFT' => 'Draft',
                        'PENDING_TENANT_SIGN' => 'Pending Tenant',
                        'PENDING_ADMIN_SIGN' => 'Pending Admin',
                        'ACTIVE' => 'Active',
                        'TERMINATED' => 'Terminated',
                        'COMPLETED' => 'Completed'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    $label = $statusLabels[$data->status] ?? $data->status;

                    return '<span class="badge bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('property_unit', function ($data) {
                    if (!$data->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    $assignment = $data->assignments->first();
                    $unitInfo = $assignment && $assignment->bed
                        ? e($assignment->bed->bed_label)
                        : 'N/A';

                    return '<div>
                                <div class="fw-semibold">' . e($data->property->name) . '</div>
                                <small class="text-muted">' . $unitInfo . '</small>
                            </div>';
                })
                ->addColumn('tenant_name', function ($data) {
                    if (!$data->tenant || !$data->tenant->profile) {
                        return '<span class="text-muted">No Tenant</span>';
                    }

                    $profile = $data->tenant->profile;
                    $fullName = trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                    $avatar = $profile->avatar
                        ? asset($profile->avatar)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" alt="avatar" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                <span>' . e($fullName) . '</span>
                            </div>';
                })
                ->addColumn('dates', function ($data) {
                    $start = date('M d, Y', strtotime($data->start_date));
                    $end = date('M d, Y', strtotime($data->end_date));

                    return '<div>
                                <span class="text-muted d-block">Start: ' . $start . '</span>
                                <span class="text-muted">End: ' . $end . '</span>
                            </div>';
                })
                ->addColumn('rent', function ($data) {
                    $frequency = str_replace('_', ' ', ucwords(strtolower($data->payment_frequency)));
                    $totalduration = $data->start_date && $data->end_date
                        ? (new \DateTime($data->end_date))->diff(new \DateTime($data->start_date))->m + 1
                        : 0;
                    $totalRent = number_format($data->rent_amount * $totalduration, 2);
                    return '<div>
                                <div class="fw-semibold">$' . $totalRent . '</div>
                                <small class="text-muted">' . $frequency . '</small>
                            </div>';
                })
                ->addColumn('signature_status', function ($data) {
                    $doc = $data->documents->first();

                    if (!$doc) {
                        return '<span class="text-muted">No Document</span>';
                    }

                    $tenantSigned = $doc->tenant_signed_at ? '✓' : '○';
                    $adminSigned = $doc->admin_signed_at ? '✓' : '○';

                    return '<div class="text-center">
                                <span class="badge badge-sm ' . ($doc->tenant_signed_at ? 'bg-success' : 'bg-secondary') . '">' . $tenantSigned . ' Tenant</span>
                                <span class="badge badge-sm ' . ($doc->admin_signed_at ? 'bg-success' : 'bg-secondary') . ' ms-1">' . $adminSigned . ' Admin</span>
                            </div>';
                })
                ->rawColumns(['status_badge', 'property_unit', 'address', 'tenant_name', 'dates', 'rent', 'signature_status'])
                ->make(true);
        }
    }

    public function create()
    {
        // OPTIMIZED: Select only needed columns for dropdowns
        $terms = Season::where('is_active', true)->select('id', 'name', 'blanket_start_date', 'blanket_end_date')->get();
        $properties = Property::with(['units:id,property_id,name'])->select('id', 'name')->get();
        $tenants = Tenant::with(['profile:id,tenant_id,first_name,last_name'])
            ->where('status', 'approved')
            ->select('id', 'email')
            ->limit(500) // Prevent memory issues with large datasets
            ->get();

        $leaseTemplates = LeaseTemplate::where('is_active', true)->select('id', 'name')->get();

        return view('backend.layouts.leases.lease.create', compact('terms', 'properties', 'tenants', 'leaseTemplates'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = [
            'property_id' => 'required|exists:properties,id',
            'bed_id' => 'nullable|exists:beds,id', // Made optional for signing without bed assignment
            'season_id' => 'required|exists:seasons,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:WEEKLY,BIWEEKLY,MONTHLY,BIMONTHLY,SEMIANNUAL,CUSTOM',
            // Single tenant per lease - one bed = one tenant
            'tenant_ids' => 'required|array|size:1',
            'tenant_ids.*' => 'exists:tenants,id',
            'lease_template_id' => 'nullable|exists:lease_templates,id',
            'assign_bed_later' => 'nullable|string', // Flag to indicate bed will be assigned later
        ];

        // Conditional validation based on payment frequency
        if ($request->payment_frequency === 'CUSTOM') {
            $rules['custom_payments'] = 'required|array|min:1';
            $rules['custom_payments.*.due_date'] = 'required|date';
            $rules['custom_payments.*.amount'] = 'required|numeric|min:0';
        } else {
            $rules['due_day'] = 'required|integer|min:1|max:28';
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            // Get the single tenant ID
            $tenantId = $request->tenant_ids[0];
            
            // Determine status based on whether it's a draft or ready for signing
            $isDraft = $request->boolean('save_as_draft');

            $status = $isDraft
                ? 'DRAFT'
                : 'PENDING_TENANT_SIGN';

            // Check if bed assignment is deferred (assign later)
            $assignBedLater = $request->boolean('assign_bed_later') || empty($request->bed_id);

            // Create the lease with single tenant
            $lease = Lease::create([
                'tenant_id' => $tenantId,
                'property_id' => $request->property_id,
                'season_id' => $request->season_id,
                'status' => $status,
                'bed_assignment_pending' => $assignBedLater,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?? $request->start_date, // For month-to-month, use start_date
                'rent_amount' => $request->rent_amount,
                'deposit_amount' => $request->deposit_amount,
                'payment_frequency' => $request->payment_frequency,
                'deposit_collected'  => $request->boolean('deposit_collected'),
                'send_for_signature' => $request->boolean('send_for_signature'),
                'send_welcome_email' => $request->boolean('send_welcome_email'),
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create lease assignment - bed_id can be null if assigning later
            if ($assignBedLater) {
                // Create assignment without bed - will be assigned later from tenant profile
                LeaseAssignment::create([
                    'lease_id' => $lease->id,
                    'bed_id' => null,
                    'assigned_at' => now(),
                    'actual_move_in' => $request->actual_move_in ?? null, // Will be set when bed is assigned
                    'is_current' => true,
                ]);
            } else {
                // Create lease assignment for the bed
                $assignlease = LeaseAssignment::create([
                    'lease_id' => $lease->id,
                    'bed_id' => $request->bed_id,
                    'assigned_at' => now(),
                    'actual_move_in' => $request->actual_move_in ?? $request->start_date,
                    'is_current' => true,
                ]);

                if($lease && $assignlease){
                    $bed = \App\Models\Bed::find($request->bed_id);
                    $bed->update(['is_occupied' => 1]);
                }
            }

            // Handle payment schedule and invoices based on payment frequency
            $isCustomPayment = $request->payment_frequency === 'CUSTOM';
            $depositCollected = $request->boolean('deposit_collected');

            if ($isCustomPayment) {
                // Custom payment schedule
                $this->generateCustomPaymentSchedule($lease, $request->custom_payments);
                // Generate custom invoices for the tenant
                $this->generateCustomInvoices($lease, $tenantId, $request->custom_payments, $depositCollected);
            } else {
                // Standard payment schedule
                if ($request->end_date) {
                    $this->generatePaymentSchedule($lease, $request->due_day, $request->first_invoice_date);
                } else {
                    // For month-to-month, create first month's payment
                    LeasePaymentSchedule::create([
                        'lease_id' => $lease->id,
                        'due_date' => $request->first_invoice_date ?? $request->start_date,
                        'amount' => $request->rent_amount,
                        'period_start' => $request->start_date,
                        'period_end' => date('Y-m-d', strtotime($request->start_date . ' +1 month')),
                        'description' => 'Monthly Rent',
                    ]);
                }

                // Generate standard invoices for the tenant
                $this->generateInvoices($lease, $tenantId, $request->due_day ?? 1, $request->first_invoice_date, $depositCollected);
            }

            Log::info('Invoices generated for lease ID: ' . $lease->id);
            
            // Create lease document if template is selected and not a draft
            if ($request->lease_template_id && !$request->boolean('save_as_draft')) {
                $template = LeaseTemplate::find($request->lease_template_id);
                Log::info('Creating lease document using template ID: ' . $request->lease_template_id);
                
                // Create single document for the tenant
                LeaseDocument::create([
                    'lease_id' => $lease->id,
                    'lease_template_id' => $request->lease_template_id,
                    'tenant_id' => $tenantId,
                    'rendered_content' => $template->pdf_path ?? '', // Will be rendered later by service
                    'status' => 'pending_signatures',
                ]);
            }

            // if (env('APP_ENV') !== 'production') {
                $tenant = Tenant::find($tenantId);
                // Generate approval token
                $tenant->generateApprovalToken();
                $tenant->refresh();

                // Password reset URL with token + email as query string
                $passResetUrl = config('app.frontend_url')
                  . "/password-setup/"
                  . $tenant->approval_token
                  . "?" . http_build_query(['email' => $tenant->email]);

                Mail::to($tenant->email)->queue(new TenantPasswordRestLinkMail($tenant, $passResetUrl));
            // }

            // Send welcome email if enabled
            if ($request->boolean('send_welcome_email')) {
                $this->sendWelcomeEmail($lease);
            }
            if($request->boolean('send_for_signature') && !$isDraft){
                // Trigger sending for signature process
                $this->sendLeaseForSignature($lease);
            }            

            DB::commit();

            // Build success message based on options
            $message = $request->input('save_as_draft')
                ? 'Lease saved as draft successfully!'
                : 'Lease created successfully and sent for signing!';
            
            if ($assignBedLater && !$request->input('save_as_draft')) {
                $message .= ' Bed assignment is pending and can be done from the tenant profile.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'lease_id' => $lease->id,
                'bed_assignment_pending' => $assignBedLater,
                'redirect_url' => route('leases.show', $lease->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create lease: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate payment schedule for the lease
     */
    private function generatePaymentSchedule(Lease $lease, int $dueDay, ?string $firstInvoiceDate = null)
    {
        $startDate = new \DateTime($lease->start_date);
        $endDate = new \DateTime($lease->end_date);

        // Use first invoice date or calculate from start date
        $currentDate = $firstInvoiceDate
            ? new \DateTime($firstInvoiceDate)
            : clone $startDate;

        $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));

        // If current due date is before start, move to next month
        if ($currentDate < $startDate) {
            $currentDate->modify('+1 month');
            $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));
        }

        while ($currentDate <= $endDate) {
            $periodStart = clone $currentDate;
            $periodEnd = clone $currentDate;
            $periodEnd->modify('+1 month')->modify('-1 day');

            // Don't exceed lease end date
            if ($periodEnd > $endDate) {
                $periodEnd = clone $endDate;
            }

            LeasePaymentSchedule::create([
                'lease_id' => $lease->id,
                'due_date' => $currentDate->format('Y-m-d'),
                'amount' => $lease->rent_amount,
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'description' => 'Monthly Rent - ' . $currentDate->format('F Y'),
            ]);

            // Move to next month
            $currentDate->modify('+1 month');
            $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));
        }
    }

    /**
     * Generate invoices for the lease
     */
    private function generateInvoices(Lease $lease, int $tenantId, int $dueDay, ?string $firstInvoiceDate = null, bool $depositCollected = false)
    {
        $startDate = new \DateTime($lease->start_date);
        $endDate = new \DateTime($lease->end_date);
        $isMonthToMonth = ($lease->start_date === $lease->end_date);

        // Use first invoice date or calculate from start date
        $currentDate = $firstInvoiceDate
            ? new \DateTime($firstInvoiceDate)
            : clone $startDate;

        $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));

        // If current due date is before start, move to next month
        if ($currentDate < $startDate) {
            $currentDate->modify('+1 month');
            $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));
        }

        $invoiceNumber = 1;
        $isFirstInvoice = true;

        // For month-to-month, only create first invoice
        $maxInvoices = $isMonthToMonth ? 1 : 999;
        $invoiceCount = 0;

        while (($isMonthToMonth || $currentDate <= $endDate) && $invoiceCount < $maxInvoices) {
            $invoiceCount++;

            // Calculate amount for this invoice
            $amount = $lease->rent_amount;
            $type = 'RENT';
            // Generate unique invoice number
            $invoiceNumberStr = 'INV-' . $lease->id . '-' . $tenantId . '-' . str_pad($invoiceNumber, 3, '0', STR_PAD_LEFT);

            // If first invoice and deposit not collected, add deposit to first invoice
            if ($isFirstInvoice && !$depositCollected && $lease->deposit_amount > 0) {

                Invoice::create([
                    'lease_id' => $lease->id,
                    'tenant_id' => $tenantId,
                    'invoice_number' => $invoiceNumberStr,
                    'amount' => $lease->deposit_amount,
                    'total_amount' => $lease->deposit_amount,
                    'balance_due' => $lease->deposit_amount,
                    'due_date' => $currentDate->format('Y-m-d'),
                    'type' => 'DEPOSIT',
                    'status' => 'UNPAID',
                    'issue_date' => now(),
                    'includes_deposit' => $depositCollected,
                ]);
            }

            Invoice::create([
                'lease_id' => $lease->id,
                'tenant_id' => $tenantId,
                'invoice_number' => $invoiceNumberStr,
                'amount' => $amount,
                'total_amount' => $amount,
                'balance_due' => $amount,
                'due_date' => $currentDate->format('Y-m-d'),
                'type' => $type,
                'status' => 'UNPAID',
                'issue_date' => now(),
                'is_first_invoice' => $isFirstInvoice,
            ]);


            $isFirstInvoice = false;
            $invoiceNumber++;

            // Move to next month
            $currentDate->modify('+1 month');
            $currentDate->setDate($currentDate->format('Y'), $currentDate->format('m'), min($dueDay, $currentDate->format('t')));
        }
    }

    /**
     * Generate custom payment schedule for the lease
     */
    private function generateCustomPaymentSchedule(Lease $lease, array $customPayments)
    {
        foreach ($customPayments as $payment) {
            LeasePaymentSchedule::create([
                'lease_id' => $lease->id,
                'due_date' => $payment['due_date'],
                'amount' => $payment['amount'],
                'period_start' => $payment['due_date'],
                'period_end' => $payment['due_date'],
                'description' => $payment['description'] ?? 'Custom Payment',
            ]);
        }
    }

    /**
     * Generate custom invoices for the lease
     */
    private function generateCustomInvoices(Lease $lease, int $tenantId, array $customPayments, bool $depositCollected = false)
    {
        // Sort payments by due date
        usort($customPayments, function($a, $b) {
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });

        $invoiceNumber = 1;
        $isFirstInvoice = true;

        foreach ($customPayments as $payment) {
            $amount = $payment['amount'];
            $type = 'RENT';

            // Generate unique invoice number
            $invoiceNumberStr = 'INV-' . $lease->id . '-' . $tenantId . '-' . str_pad($invoiceNumber, 3, '0', STR_PAD_LEFT);

            // If first invoice and deposit not collected, add deposit to first invoice
            if ($isFirstInvoice && !$depositCollected && $lease->deposit_amount > 0) {

                Invoice::create([
                    'lease_id' => $lease->id,
                    'tenant_id' => $tenantId,
                    'invoice_number' => $invoiceNumberStr,
                    'amount' => $lease->deposit_amount,
                    'total_amount' => $lease->deposit_amount,
                    'balance_due' => $lease->deposit_amount,
                    'due_date' => $payment['due_date'],
                    'type' => 'DEPOSIT',
                    'status' => 'UNPAID',
                    'issue_date' => now(),
                    'includes_deposit' => $depositCollected,
                ]);
            }

            Invoice::create([
                'lease_id' => $lease->id,
                'tenant_id' => $tenantId,
                'invoice_number' => $invoiceNumberStr,
                'amount' => $amount,
                'total_amount' => $amount,
                'balance_due' => $amount,
                'due_date' => $payment['due_date'],
                'type' => $type,
                'status' => 'UNPAID',
                'issue_date' => now(),
                'is_first_invoice' => $isFirstInvoice,
            ]);

            $isFirstInvoice = false;
            $invoiceNumber++;
        }
    }

    public function show($id)
    {
        $lease = Lease::with([
            'tenant.profile',
            'property',
            'season',
            'assignments.bed.room',
            'documents.template',
            'invoices',
            // 'payments'
        ])->findOrFail($id);

        // Get all leases for sidebar
        $leases = Lease::with([
            'tenant.profile',
            'property',
            'assignments.bed'
        ])
            ->orderBy('id', 'desc')
            ->get();

        return view('backend.layouts.leases.lease.lease-detail', compact('lease', 'leases'));
    }

    public function details($id)
    {
        $lease = Lease::with([
            'tenant.profile',
            'property',
            'season',
            'assignments.bed.room',
            'documents.template'
        ])->findOrFail($id);

        // Calculate days remaining
        $daysRemaining = max(0, now()->diffInDays($lease->end_date, false));

        // Get document status
        $document = $lease->tenant->leaseDocuments->first();
        $documentStatus = [
            'has_document' => $document ? true : false,
            'tenant_signed' => $document && $document->tenant_signed_at ? true : false,
            'admin_signed' => $document && $document->admin_signed_at ? true : false,
            'tenant_signed_date' => $document && $document->tenant_signed_at ? date('M d, Y', strtotime($document->tenant_signed_at)) : null,
            'admin_signed_date' => $document && $document->admin_signed_at ? date('M d, Y', strtotime($document->admin_signed_at)) : null,
        ];

        // Format tenant info
        $profile = $lease->tenant ? $lease->tenant->profile : null;
        $fullName = $profile ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? '')) : 'No Tenant';
        $avatar = $profile && $profile->avatar
            ? asset($profile->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($fullName) . '&background=random';

        // Format assignment
        $assignment = $lease->assignments->where('is_current', true)->first();
        $unit = $assignment && $assignment->bed ? $assignment->bed->bed_number : 'N/A';

        return response()->json([
            'success' => true,
            'lease' => [
                'id' => $lease->id,
                'status' => $lease->status,
                'tenant_name' => $fullName,
                'tenant_email' => $lease->tenant ? $lease->tenant->email : 'N/A',
                'tenant_phone' => $profile ? $profile->phone : 'N/A',
                'avatar' => $avatar,
                'property_name' => $lease->property ? $lease->property->name : 'N/A',
                'unit' => $unit,
                'address' => $lease->property ? $lease->property->address . ', ' . $lease->property->city . ', ' . $lease->property->state . ' ' . $lease->property->zip_code : 'N/A',
                'start_date' => date('M d, Y', strtotime($lease->start_date)),
                'end_date' => date('M d, Y', strtotime($lease->end_date)),
                'rent_amount' => number_format($lease->rent_amount, 2),
                'deposit_amount' => number_format($lease->deposit_amount, 2),
                'payment_frequency' => str_replace('_', ' ', ucwords(strtolower($lease->payment_frequency))),
                'days_remaining' => $daysRemaining,
                'lease_since' => date('M d, Y', strtotime($lease->created_at)),
                'document_status' => $documentStatus,
                'notes' => $lease->notes
            ]
        ]);
    }

    /**
     * Mark security deposit as collected
     */
    public function collectDeposit($id)
    {
        try {
            $lease = Lease::findOrFail($id);

            $lease->update([
                'deposit_collected' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security deposit marked as collected successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to collect deposit: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update deposit status.'
            ], 500);
        }
    }

    public function getUnits($propertyId)
    {
        $property = \App\Models\Property::with('units')->find($propertyId);
        if (!$property) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Property not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $property->units]);
    }

    public function getRooms($unitId)
    {
        $unit = \App\Models\Unit::with('rooms')->find($unitId);
        if (!$unit) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Unit not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $unit->rooms]);
    }

    public function getBeds($roomId)
    {
        $room = \App\Models\Room::with('beds')->find($roomId);
        if (!$room) {
            return response()->json(['success' => false, 'data' => [], 'message' => 'Room not found'], 404);
        }

        // Get all beds for this room with their active lease assignments
        $beds = Bed::where('room_id', $roomId)
            ->with(['leaseAssignments' => function ($query) {
                $query->where('is_current', true)
                    ->whereNull('deleted_at')
                    ->with(['lease' => function ($q) {
                        $q->whereIn('status', ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])
                          ->select('id', 'start_date', 'end_date', 'status', 'tenant_id')
                          ->with('tenant:id,email');
                    }]);
            }])
            // ->where('is_occupied', false)
            ->get();
 
        // Format the response with lease info for each bed
        $bedsData = $beds->map(function ($bed) {
            $activeAssignment = $bed->leaseAssignments->first();
            $activeLease = $activeAssignment ? $activeAssignment->lease : null;

            return [
                'id' => $bed->id,
                'bed_number' => $bed->bed_number,
                'bed_label' => $bed->bed_label ?? $bed->bed_number,
                'room_id' => $bed->room_id,
                'is_booked' => $activeLease ? true : false,
                'lease_info' => $activeLease ? [
                    'lease_id' => $activeLease->id,
                    'status' => $activeLease->status,
                    'start_date' => $activeLease->start_date,
                    'end_date' => $activeLease->end_date,
                    'start_date_formatted' => date('M d, Y', strtotime($activeLease->start_date)),
                    'end_date_formatted' => date('M d, Y', strtotime($activeLease->end_date)),
                    'tenant_email' => $activeLease->tenant ? $activeLease->tenant->email : null,
                ] : null,
            ];
        });

        // Log::info('Beds for room ID ' . $roomId . ': ' . $bedsData->toJson());
        return response()->json(['success' => true, 'data' => $bedsData]);
    }

    private function sendWelcomeEmail(Lease $lease)
    {
        try {
            // Load necessary relationships
            $lease->load(['tenant', 'property']);
            
            // Check if tenant exists
            if (!$lease->tenant) {
                Log::warning('Cannot send welcome email - No tenant associated with lease ID: ' . $lease->id);
                return;
            }

            // Check if welcome email should be sent
            if (!$lease->send_welcome_email) {
                Log::info('Welcome email not sent - send_welcome_email flag is false for lease ID: ' . $lease->id);
                return;
            }

            // Send the welcome email
            Mail::to($lease->tenant->email)->send(new TenantWelcomeMail($lease));
            
            Log::info('Welcome email sent successfully for lease ID: ' . $lease->id . ' to ' . $lease->tenant->email);
            
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email for lease ID: ' . $lease->id . ' - Error: ' . $e->getMessage());
            
            // Optional: You might want to throw the exception or handle it differently
            // throw $e;
        }
    }

    private function sendLeaseForSignature(Lease $lease)
    {
        try {
            // Load necessary relationships
            $lease->load(['tenant.profile', 'property', 'documents']);
            
            // Check if tenant exists
            if (!$lease->tenant) {
                Log::warning('Cannot send signature request - No tenant associated with lease ID: ' . $lease->id);
                return;
            }

            // Get the lease document
            $leaseDocument = $lease->documents->first();
            
            if (!$leaseDocument) {
                Log::warning('Cannot send signature request - No document found for lease ID: ' . $lease->id);
                return;
            }

            // Update document status to pending signatures
            $leaseDocument->update([
                'status' => 'pending_signatures'
            ]);

            // Send the signature request email
            Mail::to($lease->tenant->email)->send(new LeaseSignatureRequestMail($lease, $leaseDocument));
            
            Log::info('Lease signature request email sent successfully for lease ID: ' . $lease->id . ' to ' . $lease->tenant->email);
            
        } catch (\Exception $e) {
            Log::error('Failed to send lease signature request for lease ID: ' . $lease->id . ' - Error: ' . $e->getMessage());
        }
    }

    /**
     * Resend lease for signature
     */
    public function resendForSignature($id)
    {
        try {
            $lease = Lease::with(['tenant.profile', 'property', 'documents'])->findOrFail($id);
            
            $this->sendLeaseForSignature($lease);

            return response()->json([
                'success' => true,
                'message' => 'Lease signature request has been resent to ' . $lease->tenant->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend lease for signature: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend signature request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBedsByProperty($id)
    {
        $beds = Bed::whereHas('room', function ($query) use ($id) {
            $query->whereHas('unit', function ($q) use ($id) {
                $q->where('property_id', $id);
            });
        })->get();

        return response()->json(['success' => true, 'data' => $beds]);
    }

    /**
     * Get lease data for manual close modal
     */
    public function getCloseData($id)
    {
        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed');
                },
                'invoices' => function ($q) {
                    $q->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
                }
            ])->findOrFail($id);

            $profile = $lease->tenant?->profile;
            $tenantName = $profile 
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'No Tenant';

            $assignment = $lease->assignments->first();
            $bedLabel = $assignment?->bed?->bed_label ?? 'N/A';

            // Get unpaid invoices
            $unpaidInvoices = $lease->invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
                    'due_date' => date('M d, Y', strtotime($invoice->due_date)),
                    'amount' => $invoice->total_amount ?? $invoice->amount,
                    'balance_due' => $invoice->balance_due ?? ($invoice->total_amount - ($invoice->paid_amount ?? 0)),
                    'status' => $invoice->isOverdue() ? 'OVERDUE' : $invoice->status,
                ];
            });

            return response()->json([
                'success' => true,
                'lease' => [
                    'id' => $lease->id,
                    'tenant_name' => $tenantName,
                    'property_name' => $lease->property?->name ?? 'N/A',
                    'bed_label' => $bedLabel,
                    'start_date' => date('M d, Y', strtotime($lease->start_date)),
                    'end_date' => date('M d, Y', strtotime($lease->end_date)),
                    'original_end_date' => $lease->end_date->format('Y-m-d'),
                    'rent_amount' => $lease->rent_amount,
                    'status' => $lease->status,
                ],
                'unpaid_invoices' => $unpaidInvoices,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get lease close data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lease data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually close a lease
     */
    public function closeLease(Request $request, $id)
    {
        $request->validate([
            'end_date' => 'required|date',
            'close_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'send_notifications' => 'boolean',
        ]);

        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed');
                },
                'invoices' => function ($q) {
                    $q->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE']);
                }
            ])->findOrFail($id);

            // Check for unpaid invoices
            if ($lease->invoices->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot close lease with unpaid invoices. Please resolve all outstanding invoices first.'
                ], 422);
            }

            // Check if lease is already completed
            if ($lease->status === 'COMPLETED') {
                return response()->json([
                    'success' => false,
                    'message' => 'This lease is already closed.'
                ], 422);
            }

            DB::beginTransaction();

            // 1. Update lease status and end date
            $oldEndDate = $lease->end_date;
            $closeReason = $request->close_reason;
            $notes = $request->notes;
            
            // Append close reason to notes
            $updatedNotes = $lease->notes ?? '';
            if ($closeReason || $notes) {
                $updatedNotes .= "\n\n--- Manual Closure (" . now()->format('M d, Y H:i') . ") ---\n";
                if ($closeReason) {
                    $updatedNotes .= "Reason: " . ucwords(str_replace('_', ' ', $closeReason)) . "\n";
                }
                if ($notes) {
                    $updatedNotes .= "Notes: " . $notes;
                }
            }

            $lease->update([
                'status' => 'COMPLETED',
                'end_date' => $request->end_date,
                'notes' => trim($updatedNotes),
            ]);

            Log::info("Lease #{$lease->id} manually closed. End date changed from {$oldEndDate} to {$request->end_date}");

            // 2. Update all lease assignments
            $lease->assignments()->where('is_current', true)->update([
                'is_current' => false,
                'actual_move_out' => $request->end_date,
            ]);

            // 3. Mark beds as unoccupied
            $bedIds = $lease->assignments->pluck('bed_id')->filter()->toArray();
            if (!empty($bedIds)) {
                Bed::whereIn('id', $bedIds)->update([
                    'is_occupied' => false,
                ]);
                Log::info("Beds marked as unoccupied: " . implode(', ', $bedIds));
            }

            DB::commit();

            // 4. Send notification emails if enabled
            if ($request->boolean('send_notifications')) {
                try {
                    // Reload lease with fresh data
                    $lease->refresh();
                    $lease->load(['tenant.profile', 'property', 'assignments.bed']);

                    // Send to tenant
                    if ($lease->tenant?->email) {
                        Mail::to($lease->tenant->email)->send(new \App\Mail\Tenant\LeaseCompletedMail($lease));
                        Log::info("Lease completion email sent to tenant: {$lease->tenant->email}");
                    }

                    // Send to admin
                    $adminEmail = config('mail.admin_email');
                    if ($adminEmail) {
                        Mail::to($adminEmail)->send(new \App\Mail\Tenant\LeaseCompletedAdminMail($lease));
                        Log::info("Lease completion email sent to admin: {$adminEmail}");
                    }
                } catch (\Exception $mailError) {
                    Log::error("Failed to send lease completion emails: " . $mailError->getMessage());
                    // Don't fail the request if emails fail
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Lease has been closed successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to close lease: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to close lease: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lease data for change bed modal
     */
    public function getChangeBedData($id)
    {
        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed.room.unit');
                },
            ])->findOrFail($id);

            // Check if lease is active
            if (!in_array($lease->status, ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bed can only be changed for active or pending leases.'
                ], 422);
            }

            $profile = $lease->tenant?->profile;
            $tenantName = $profile 
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'No Tenant';

            $currentAssignment = $lease->assignments->first();
            $currentBed = $currentAssignment?->bed;
            $currentBedLabel = $currentBed?->bed_label ?? 'N/A';

            // Get available beds for the same property (excluding current bed)
            $availableBeds = Bed::with(['room.unit'])
                ->whereHas('room.unit', function ($query) use ($lease) {
                    $query->where('property_id', $lease->property_id);
                })
                ->where('is_occupied', false)
                ->orWhere(function ($query) use ($currentBed) {
                    // Include current bed in the list
                    if ($currentBed) {
                        $query->where('id', $currentBed->id);
                    }
                })
                ->get()
                ->map(function ($bed) use ($currentBed) {
                    $roomName = $bed->room?->room_number ?? $bed->room?->name ?? '';
                    $unitName = $bed->room?->unit?->name ?? $bed->room?->unit?->unit_number ?? '';
                    $label = $bed->bed_label;
                    if ($unitName) {
                        $label = $unitName . ' - ' . $label;
                    }
                    if ($roomName) {
                        $label .= ' (Room: ' . $roomName . ')';
                    }
                    
                    return [
                        'id' => $bed->id,
                        'bed_label' => $label,
                        'base_rent' => $bed->base_rent,
                        'is_current' => $currentBed && $bed->id === $currentBed->id,
                    ];
                });

            return response()->json([
                'success' => true,
                'lease' => [
                    'id' => $lease->id,
                    'tenant_name' => $tenantName,
                    'property_name' => $lease->property?->name ?? 'N/A',
                    'current_bed_id' => $currentBed?->id,
                    'current_bed_label' => $currentBedLabel,
                    'start_date' => date('M d, Y', strtotime($lease->start_date)),
                    'end_date' => date('M d, Y', strtotime($lease->end_date)),
                    'rent_amount' => $lease->rent_amount,
                    'status' => $lease->status,
                ],
                'available_beds' => $availableBeds,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get change bed data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lease data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change bed assignment for a lease
     */
    public function changeBed(Request $request, $id)
    {
        $request->validate([
            'new_bed_id' => 'required|exists:beds,id',
            'effective_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed');
                },
            ])->findOrFail($id);

            // Check if lease is active
            if (!in_array($lease->status, ['ACTIVE', 'PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bed can only be changed for active or pending leases.'
                ], 422);
            }

            $newBed = Bed::findOrFail($request->new_bed_id);
            $currentAssignment = $lease->assignments->first();
            $oldBed = $currentAssignment?->bed;

            // Check if the new bed is the same as current
            if ($oldBed && $oldBed->id === $newBed->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected bed is already assigned to this lease.'
                ], 422);
            }

            // Check if new bed is available
            if ($newBed->is_occupied) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected bed is not available. Please choose another bed.'
                ], 422);
            }

            // Check if new bed belongs to the same property
            $newBedPropertyId = $newBed->room?->unit?->property_id;
            if ($newBedPropertyId !== $lease->property_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected bed does not belong to the same property.'
                ], 422);
            }

            DB::beginTransaction();

            // 1. Mark old assignment as not current
            if ($currentAssignment) {
                $currentAssignment->update([
                    'is_current' => false,
                    'actual_move_out' => $request->effective_date,
                ]);
            }

            // 2. Mark old bed as unoccupied
            if ($oldBed) {
                $oldBed->update(['is_occupied' => false]);
                Log::info("Bed #{$oldBed->id} ({$oldBed->bed_label}) marked as unoccupied for lease #{$lease->id}");
            }

            // 3. Create new assignment
            $newAssignment = LeaseAssignment::create([
                'lease_id' => $lease->id,
                'bed_id' => $newBed->id,
                'assigned_at' => $request->effective_date,
                'actual_move_in' => $request->effective_date,
                'is_current' => true,
            ]);

            // 4. Mark new bed as occupied
            $newBed->update(['is_occupied' => true]);
            Log::info("Bed #{$newBed->id} ({$newBed->bed_label}) marked as occupied for lease #{$lease->id}");

            // 5. Add note to lease
            $notes = $lease->notes ?? '';
            $changeNote = "\n\n--- Bed Change (" . now()->format('M d, Y H:i') . ") ---\n";
            $changeNote .= "From: " . ($oldBed?->bed_label ?? 'N/A') . "\n";
            $changeNote .= "To: " . $newBed->bed_label . "\n";
            $changeNote .= "Effective: " . date('M d, Y', strtotime($request->effective_date)) . "\n";
            if ($request->notes) {
                $changeNote .= "Notes: " . $request->notes;
            }
            
            $lease->update([
                'notes' => trim($notes . $changeNote),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bed assignment has been changed successfully.',
                'new_bed_label' => $newBed->bed_label,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to change bed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to change bed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data for initial bed assignment (for leases created without bed)
     */
    public function getAssignBedData($id)
    {
        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed.room.unit');
                },
            ])->findOrFail($id);

            // Check if lease needs bed assignment
            if (!$lease->bed_assignment_pending && $lease->assignments->first()?->bed_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This lease already has a bed assigned. Use "Change Bed" to modify.'
                ], 422);
            }

            $profile = $lease->tenant?->profile;
            $tenantName = $profile 
                ? trim($profile->first_name . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''))
                : 'No Tenant';

            // Get available beds for this property
            $availableBeds = Bed::whereHas('room.unit', function ($q) use ($lease) {
                    $q->where('property_id', $lease->property_id);
                })
                ->where('is_occupied', false)
                ->with('room.unit')
                ->get()
                ->map(function ($bed) {
                    $roomName = $bed->room?->room_number;
                    $unitName = $bed->room?->unit?->unit_number;
                    
                    $label = $bed->bed_label ?? $bed->bed_number;
                    if ($unitName) {
                        $label .= ' (Unit: ' . $unitName;
                        if ($roomName) {
                            $label .= ', Room: ' . $roomName;
                        }
                        $label .= ')';
                    } elseif ($roomName) {
                        $label .= ' (Room: ' . $roomName . ')';
                    }
                    
                    return [
                        'id' => $bed->id,
                        'bed_label' => $label,
                        'base_rent' => $bed->base_rent,
                    ];
                });

            return response()->json([
                'success' => true,
                'lease' => [
                    'id' => $lease->id,
                    'tenant_name' => $tenantName,
                    'tenant_email' => $lease->tenant?->email ?? 'N/A',
                    'property_name' => $lease->property?->name ?? 'N/A',
                    'start_date' => date('M d, Y', strtotime($lease->start_date)),
                    'end_date' => date('M d, Y', strtotime($lease->end_date)),
                    'rent_amount' => $lease->rent_amount,
                    'status' => $lease->status,
                ],
                'available_beds' => $availableBeds,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get assign bed data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load lease data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign bed to a lease (initial assignment for leases created without bed)
     */
    public function assignBed(Request $request, $id)
    {
        $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'move_in_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $lease = Lease::with([
                'tenant.profile',
                'property',
                'assignments' => function ($q) {
                    $q->where('is_current', true)->with('bed');
                },
            ])->findOrFail($id);

            // Check if lease needs bed assignment
            $currentAssignment = $lease->assignments->first();
            if (!$lease->bed_assignment_pending && $currentAssignment?->bed_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This lease already has a bed assigned. Use "Change Bed" to modify.'
                ], 422);
            }

            $bed = Bed::findOrFail($request->bed_id);

            // Check if bed is available
            if ($bed->is_occupied) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected bed is not available. Please choose another bed.'
                ], 422);
            }

            // Check if bed belongs to the same property
            $bedPropertyId = $bed->room?->unit?->property_id;
            if ($bedPropertyId !== $lease->property_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected bed does not belong to the lease property.'
                ], 422);
            }

            DB::beginTransaction();

            // Update existing assignment or create new one
            if ($currentAssignment) {
                $currentAssignment->update([
                    'bed_id' => $bed->id,
                    'assigned_at' => now(),
                    'actual_move_in' => $request->move_in_date,
                ]);
            } else {
                LeaseAssignment::create([
                    'lease_id' => $lease->id,
                    'bed_id' => $bed->id,
                    'assigned_at' => now(),
                    'actual_move_in' => $request->move_in_date,
                    'is_current' => true,
                ]);
            }

            // Mark bed as occupied
            $bed->update(['is_occupied' => true]);
            Log::info("Bed #{$bed->id} ({$bed->bed_label}) assigned to lease #{$lease->id}");

            // Update lease to mark bed assignment as complete
            $notes = $lease->notes ?? '';
            $assignNote = "\n\n--- Bed Assigned (" . now()->format('M d, Y H:i') . ") ---\n";
            $assignNote .= "Bed: " . $bed->bed_label . "\n";
            $assignNote .= "Move-in Date: " . date('M d, Y', strtotime($request->move_in_date)) . "\n";
            if ($request->notes) {
                $assignNote .= "Notes: " . $request->notes;
            }

            $lease->update([
                'bed_assignment_pending' => false,
                'notes' => trim($notes . $assignNote),
            ]);

            // If lease is in a signing status, it can now proceed to active on signing
            // Optionally activate the lease if it's fully signed
            if ($lease->status === 'PENDING_ADMIN_SIGN') {
                // Check if all signatures are complete
                $document = $lease->documents()->first();
                if ($document && $document->tenant_signed_at && $document->admin_signed_at) {
                    $lease->update(['status' => 'ACTIVE']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bed has been assigned successfully. Tenant can now move in.',
                'bed_label' => $bed->bed_label,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign bed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign bed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leases pending bed assignment for a tenant
     */
    public function getPendingBedAssignments($tenantId)
    {
        try {
            $leases = Lease::with(['property', 'season'])
                ->where('tenant_id', $tenantId)
                ->where('bed_assignment_pending', true)
                ->whereIn('status', ['PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN', 'ACTIVE', 'DRAFT'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($lease) {
                    return [
                        'id' => $lease->id,
                        'property_name' => $lease->property?->name ?? 'N/A',
                        'start_date' => $lease->start_date->format('M d, Y'),
                        'end_date' => $lease->end_date->format('M d, Y'),
                        'rent_amount' => $lease->rent_amount,
                        'status' => $lease->status,
                        'created_at' => $lease->created_at->format('M d, Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'leases' => $leases,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get pending bed assignments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }
}
