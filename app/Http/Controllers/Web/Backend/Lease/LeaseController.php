<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use App\Models\Lease;
use App\Models\Season;
use App\Models\Tenant;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\LeaseAssignment;
use App\Models\LeasePaymentSchedule;
use App\Models\Lease\LeaseDocument;
use App\Models\Lease\LeaseTemplate;
use Yajra\DataTables\Facades\DataTables;

class LeaseController extends Controller
{
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

        return view('backend.layouts.leases.lease.index', compact('stats'));
    }

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
                    'property:id,name,address,city,state,zip_code',
                    'assignments' => function ($q) {
                        $q->select('id', 'lease_id', 'bed_id', 'is_current')
                            ->where('is_current', true)
                            ->whereNull('deleted_at')
                            ->with('bed:id,bed_number,room_id');
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

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('leases.start_date', '>=', $request->date_from);
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
                        ? e($assignment->bed->bed_number)
                        : 'N/A';

                    return '<div>
                                <div class="fw-semibold">' . e($data->property->name) . '</div>
                                <small class="text-muted">' . $unitInfo . '</small>
                            </div>';
                })
                ->addColumn('address', function ($data) {
                    if (!$data->property) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    $address = e($data->property->address);
                    $location = e($data->property->city . ', ' . $data->property->state . ' ' . $data->property->zip_code);

                    return '<div>
                                <small class="text-muted d-block">' . $address . '</small>
                                <small class="text-muted">' . $location . '</small>
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
                                <small class="text-muted d-block">Start: ' . $start . '</small>
                                <small class="text-muted">End: ' . $end . '</small>
                            </div>';
                })
                ->addColumn('rent', function ($data) {
                    $frequency = str_replace('_', ' ', ucwords(strtolower($data->payment_frequency)));
                    return '<div>
                                <div class="fw-semibold">$' . number_format($data->rent_amount, 2) . '</div>
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
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'bed_id' => 'required|exists:beds,id',
            'season_id' => 'required|exists:seasons,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:WEEKLY,BIWEEKLY,MONTHLY,BIMONTHLY,SEMIANNUAL,CUSTOM',
            'due_day' => 'required|integer|min:1|max:28',
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'exists:tenants,id',
            'lease_template_id' => 'nullable|exists:lease_templates,id',
        ]);

        DB::beginTransaction();

        try {
            // Determine status based on whether it's a draft or ready for signing
            $status = $request->input('save_as_draft') ? 'DRAFT' : 'PENDING_TENANT_SIGN';

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
                'deposit_collected' => $request->has('deposit_collected') ? true : false,
                'send_for_signature' => $request->has('send_for_signature') ? true : false,
                'send_welcome_email' => $request->has('send_welcome_email') ? true : false,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create lease assignment for the bed
            LeaseAssignment::create([
                'lease_id' => $lease->id,
                'bed_id' => $request->bed_id,
                'assigned_at' => now(),
                'actual_move_in' => $request->start_date,
                'is_current' => true,
            ]);

            // Generate payment schedule if not month-to-month
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

            // Create lease document if template is selected and not a draft
            if ($request->lease_template_id && !$request->input('save_as_draft')) {
                $template = LeaseTemplate::find($request->lease_template_id);
                
                // Create document for each tenant
                foreach ($request->tenant_ids as $tenantId) {
                    LeaseDocument::create([
                        'lease_id' => $lease->id,
                        'lease_template_id' => $request->lease_template_id,
                        'tenant_id' => $tenantId,
                        'rendered_content' => $template->content ?? '', // Will be rendered later by service
                        'status' => 'pending_signatures',
                    ]);
                }
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

    public function show($id)
    {
        $lease = Lease::with([
            'tenant.profile',
            'property',
            'season',
            'assignments.bed.room',
            'documents.template',
            'invoices',
            'payments'
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
}
