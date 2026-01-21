<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use App\Models\Bed;
use App\Models\Lease;
use App\Models\Season;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Property;
use Illuminate\Http\Request;
use App\Models\LeaseAssignment;
use Illuminate\Support\Facades\DB;
use App\Models\Lease\LeaseDocument;
use App\Models\Lease\LeaseTemplate;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\LeasePaymentSchedule;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\TenantApplication\TenantWelcomeMail;
use App\Mail\Tenant\LeaseSignatureRequestMail;

class LeaseController extends Controller
{
    /**
     * lease index page
     */
    public function index(Request $request)
    {
        // Get statistics
        $stats = [
            'active' => Lease::where('status', 'ACTIVE')->count(),
            'inProcess' => Lease::whereIn('status', ['PENDING_TENANT_SIGN', 'PENDING_ADMIN_SIGN'])->count(),
            'future' => Lease::where('status', 'DRAFT')->where('start_date', '>', now())->count(),
            'expiring' => Lease::where('status', 'ACTIVE')
                ->whereBetween('end_date', [now(), now()->addDays(90)])
                ->count(),
            'expired' => Lease::whereIn('status', ['TERMINATED', 'COMPLETED'])->count()
        ];

        $properties = Property::where('is_active', true)->get();
        $tenants = Tenant::all();

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
        $terms = Season::where('is_active', true)->get();
        $properties = Property::with(['units'])->get();
        $tenants = Tenant::with(['profile'])->where('status', 'active')->get();

        $leaseTemplates = LeaseTemplate::where('is_active', true)->get();

        return view('backend.layouts.leases.lease.create', compact('terms', 'properties', 'tenants', 'leaseTemplates'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $rules = [
            'property_id' => 'required|exists:properties,id',
            'bed_id' => 'required|exists:beds,id',
            'season_id' => 'required|exists:seasons,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:WEEKLY,BIWEEKLY,MONTHLY,BIMONTHLY,SEMIANNUAL,CUSTOM',
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'exists:tenants,id',
            'lease_template_id' => 'nullable|exists:lease_templates,id',
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
            // Determine status based on whether it's a draft or ready for signing
            $isDraft = $request->boolean('save_as_draft');

            $status = $isDraft
                ? 'DRAFT'
                : 'PENDING_TENANT_SIGN';


            // Create the lease
            $lease = Lease::create([
                'tenant_id' => $request->tenant_ids[0], // Primary tenant
                'property_id' => $request->property_id,
                'season_id' => $request->season_id,
                'status' => $status,
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

            // Create lease assignment for the bed
            $assignlease = LeaseAssignment::create([
                'lease_id' => $lease->id,
                'bed_id' => $request->bed_id,
                'assigned_at' => now(),
                'actual_move_in' => $request->start_date,
                'is_current' => true,
            ]);

            if($lease && $assignlease){
                $bed = \App\Models\Bed::find($request->bed_id);
                $bed->update(['status' => 'OCCUPIED']);
            }

            // Handle payment schedule and invoices based on payment frequency
            $isCustomPayment = $request->payment_frequency === 'CUSTOM';
            $depositCollected = $request->boolean('deposit_collected');

            if ($isCustomPayment) {
                // Custom payment schedule
                $this->generateCustomPaymentSchedule($lease, $request->custom_payments);

                // Generate custom invoices for each tenant
                foreach ($request->tenant_ids as $tenantId) {
                    $this->generateCustomInvoices($lease, $tenantId, $request->custom_payments, $depositCollected);
                }
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

                // Generate standard invoices for each tenant
                foreach ($request->tenant_ids as $tenantId) {
                    $this->generateInvoices($lease, $tenantId, $request->due_day ?? 1, $request->first_invoice_date, $depositCollected);
                }
            }

            Log::info('Invoices generated for lease ID: ' . $lease->id);
            // Create lease document if template is selected and not a draft
           if ($request->lease_template_id && !$request->boolean('save_as_draft')) {
                $template = LeaseTemplate::find($request->lease_template_id);
                Log::info('Creating lease document using template ID: ' . $request->lease_template_id);
                // Create document for each tenant
                foreach ($request->tenant_ids as $tenantId) {
                    LeaseDocument::create([
                        'lease_id' => $lease->id,
                        'lease_template_id' => $request->lease_template_id,
                        'tenant_id' => $tenantId,
                        'rendered_content' => $template->pdf_path ?? '', // Will be rendered later by service
                        'status' => 'pending_signatures',
                    ]);
                }
            }

            // Send welcome email if enabled
            if ($request->boolean('send_welcome_email')) {
                $this->sendWelcomeEmail($lease);
            }
            if($request->boolean('send_for_signature') && !$isDraft){
                // Trigger sending for signature process
                $this->sendLeaseForSignature($lease);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->input('save_as_draft')
                    ? 'Lease saved as draft successfully!'
                    : 'Lease created successfully and sent for signing!',
                'lease_id' => $lease->id,
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
        $document = $lease->documents->first();
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
}
