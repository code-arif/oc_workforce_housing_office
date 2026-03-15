<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\TenantApplication\TenantFormLinkMail;
use App\Models\Application;
use App\Models\ReservationRequest;
use App\Models\Tenant;
use App\Models\TenantAddress;
use App\Models\TenantDocument;
use App\Models\TenantEmergencyContact;
use App\Models\TenantEmploymentHistory;
use App\Models\TenantProfile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{

    /**
     * Application list
     */
    public function index()
    {
        return view('backend.layouts.tenants.applications.index');
    }

    /**
     * Application list data
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = Application::query()
                ->select([
                    'applications.*'
                ])
                ->orderBy('applications.id', 'desc');


            // Status filter
            if ($request->filled('status')) {
                $query->where('applications.status', $request->status);
            }

            // Search filter
            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('company_name', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('applications.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('applications.created_at', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('applicant', function ($data) {

                    return '<div class="text-truncate">
                                <div class="fw-semibold">' . e($data->email) . '</div>
                                <small class="text-muted">' . ($data->first_name ? e($data->first_name . ' ' . $data->last_name) : 'Name not provided') . '</small>
                            </div>';
                })
                ->addColumn('contact', function ($data) {
                    return '<div class="text-truncate">
                                <div>' . e($data->email) . '</div>
                                <small class="text-muted">' . e($data->phone ?? 'No phone') . '</small>
                            </div>';
                })
                ->addColumn('details', function ($data) {

                    return '<span class="text-muted">Email-only application</span>';
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'pending' => 'warning',
                        'under_review' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    ];

                    $color = $statusColors[$data->status] ?? 'secondary';
                    return '<span class="badge p-3 bg-' . $color . '">' . e(ucfirst(str_replace('_', ' ', $data->status))) . '</span>';
                })
                ->addColumn('submitted_at', function ($data) {
                    return '<div class="text-truncate">
                                <div>' . $data->created_at->format('M d, Y') . '</div>
                                <small class="text-muted">' . $data->created_at->format('h:i A') . '</small>
                            </div>';
                })
                ->addColumn('action', function ($data) {
                    $btn = '<div class="btn-group" role="group">';

                    // Single email - view details button always visible
                    $btn .= '<button type="button" onclick="viewSingleEmailDetails(' . $data->id . ')" class="btn btn-sm btn-info d-inline-flex align-items-center" title="View Details">
                                    <i class="fe fe-eye"></i>
                                </button>';

                    if ($data->status === 'pending') {
                        // Approve button
                        $btn .= '<button type="button" onclick="approveSingleEmail(' . $data->id . ')" class="btn btn-sm btn-success d-inline-flex align-items-center" title="Approve & Send Form">
                                        <i class="fe fe-check"></i>
                                    </button>';

                        // Reject button
                        $btn .= '<button type="button" onclick="rejectApplication(' . $data->id . ')" class="btn btn-sm btn-danger d-inline-flex align-items-center" title="Reject">
                                    <i class="fe fe-x"></i>
                                </button>';
                    }


                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['applicant', 'contact', 'details', 'status_badge', 'submitted_at', 'action'])
                ->make(true);
        }

        return abort(404);
    }

    /**
     * Reservation application list data
     */
    public function getReservationData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {

            $query = ReservationRequest::query()
                ->select(['reservation_requests.*'])
                ->orderBy('reservation_requests.id', 'desc');

            // Status filter
            if ($request->filled('status')) {
                $query->where('reservation_requests.status', $request->status);
            }

            // Search filter
            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('company_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%")
                        ->orWhere('industry', 'like', "%{$keyword}%");
                });
            }

            // Date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('reservation_requests.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('reservation_requests.created_at', '<=', $request->date_to);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('applicant', function ($data) {
                    $contactPerson = trim(($data->first_name ?? '') . ' ' . ($data->last_name ?? ''));
                    return '<div>
                                <div class="fw-semibold">' . e($data->company_name) . '</div>
                                <small class="text-muted">' . ($contactPerson ? e($contactPerson) : 'No contact name') . '</small>
                            </div>';
                })
                ->addColumn('contact', function ($data) {
                    return '<div>
                                <div>' . e($data->email ?? 'No email') . '</div>
                                <small class="text-muted">' . e($data->phone ?? 'No phone') . '</small>
                            </div>';
                })
                ->addColumn('details', function ($data) {
                    $industry = $data->industry ? '<span class="p-3 badge bg-secondary me-1">' . e($data->industry) . '</span>' : '';
                    $empCount = $data->employee_count ? '<small class="text-muted"><i class="fe fe-users me-1"></i>' . e($data->employee_count) . ' employees</small>' : '';
                    return '<div>' . $industry . ($empCount ? '<br>' . $empCount : '') . '</div>';
                })
                ->addColumn('status_badge', function ($data) {
                    $statusColors = [
                        'pending'   => 'warning',
                        'contacted' => 'info',
                        'accepted'  => 'success',
                        'declined'  => 'danger',
                    ];
                    $color = $statusColors[$data->status] ?? 'secondary';
                    return '<span class="badge p-3 bg-' . $color . '">' . e(ucfirst($data->status)) . '</span>';
                })
                ->addColumn('submitted_at', function ($data) {
                    return '<div>
                                <div>' . $data->created_at->format('M d, Y') . '</div>
                                <small class="text-muted">' . $data->created_at->format('h:i A') . '</small>
                            </div>';
                })
                ->addColumn('action', function ($data) {
                    $btn = '<div class="d-flex gap-1">';

                    // View button — always visible
                    $btn .= '<button type="button" onclick="seeReservation(' . $data->id . ')"
                                class="btn btn-sm btn-info d-inline-flex align-items-center" title="View Details">
                                <i class="fe fe-eye"></i>
                            </button>';

                    // Status update dropdown
                    $btn .= '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" title="Update Status">
                                    <i class="fe fe-settings"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">';

                    $statuses = ['pending' => 'warning', 'contacted' => 'info', 'accepted' => 'success', 'declined' => 'danger'];
                    foreach ($statuses as $status => $color) {
                        $activeClass = $data->status === $status ? ' fw-bold' : '';
                        $btn .= '<li><a class="dropdown-item' . $activeClass . '" href="#"
                                    onclick="updateReservationStatus(' . $data->id . ', \'' . $status . '\'); return false;">
                                    <span class="badge bg-' . $color . ' me-1">&nbsp;</span>' . ucfirst($status) .
                            ($data->status === $status ? ' <i class="fe fe-check ms-1"></i>' : '') .
                            '</a></li>';
                    }

                    $btn .= '</ul></div>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['applicant', 'contact', 'details', 'status_badge', 'submitted_at', 'action'])
                ->make(true);
        }

        return abort(404);
    }

    /**
     * Approve application
     * Creates tenant from application data with under_review status and sends form link
     */
    public function approveSingleEmail($id)
    {
        try {
            DB::beginTransaction();

            $application = Application::findOrFail($id);

            if ($application->status !== 'pending') {
                return response()->json(['message' => 'This application has already been processed.'], 400);
            }

            // Check if tenant already exists
            $existingTenant = Tenant::where('email', $application->email)->first();
            if ($existingTenant) {
                return response()->json(['message' => 'A tenant with this email already exists.'], 400);
            }

            // Create tenant from application data
            $tenant = Tenant::create([
                'application_id'     => $application->id,
                'application_source' => 'self',
                'email'              => $application->email,
                'status'             => 'under_review',
                'date_of_birth'      => $application->date_of_birth,
                'arrival_date'       => $application->arrival_date,
                'move_in_date'     => $application->departure_date,
                'password'           => Hash::make($application->application_number),
                'gender'             => $application->gender ?? null,
            ]);

            // Create tenant profile from application data
            TenantProfile::create([
                'tenant_id'   => $tenant->id,
                'first_name'  => $application->first_name ?? '',
                'middle_name' => $application->middle_name,
                'last_name'   => $application->last_name,
                'phone'       => $application->phone,
            ]);

            // Create tenant address from application data
            TenantAddress::create([
                'tenant_id' => $tenant->id,
                'address'   => $application->country_of_origin ?? null,
            ]);

            TenantEmergencyContact::create([
                'tenant_id'    => $tenant->id,
                'name'         => $application->sponsor_name ?? null,
                'phone'        => $application->sponsor_phone ?? null,
                'email'        => $application->sponsor_email ?? null,
            ]);

            if (!empty($application->employer_info) && is_array($application->employer_info)) {
                foreach ($application->employer_info as $employment) {
                    TenantEmploymentHistory::create([
                        'tenant_id'   => $tenant->id,
                        'employer' => $employment['company_name'] ?? null,
                        'title'     => $employment['job_title'] ?? null,
                        'contact_person_name'       => $employment['employer_contact_person_name'] ?? null,
                        'contact_email'             => $employment['employer_contact_person_email'] ?? null,
                        'contact_phone'             => $employment['employer_contact_person_phone'] ?? null,
                    ]);
                }
            } else {
                TenantEmploymentHistory::create([
                    'tenant_id'   => $tenant->id,
                    'employer' => $application->employer_name ?? null,
                    'title'     => $application->job_title ?? null,
                    'contact_person_name'       => $application->employer_contact_person_name ?? null,
                    'contact_email'             => $application->employer_contact_person_email ?? null,
                    'contact_phone'             => $application->employer_contact_person_phone ?? null,
                ]);
            }

            // Transfer documents from application to tenant documents
            $documentMap = [
                'passport_copy'     => 'passport',
                'visa_document'     => 'visa',
                'front_id_document' => 'id_front',
                'back_id_document'  => 'id_back',
            ];

            foreach ($documentMap as $appField => $docType) {
                if (!empty($application->$appField)) {
                    TenantDocument::create([
                        'tenant_id'     => $tenant->id,
                        'document_type' => $docType,
                        'file_path'     => $application->$appField,
                    ]);
                }
            }

            // Update application status to approved
            $application->status = 'approved';
            $application->save();

            DB::commit();

            return response()->json([
                'message'   => 'Application approved. Tenant created with under_review status',
                'tenant_id' => $tenant->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Single email approval failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to approve application.'], 500);
        }
    }

    /**
     * Reject application
     */
    public function reject($id)
    {
        try {
            $application = Application::findOrFail($id);

            if ($application->status !== 'pending') {
                return response()->json(['message' => 'This application has already been processed.'], 400);
            }

            $application->status = 'rejected';
            $application->save();

            return response()->json(['message' => 'Application rejected successfully.']);
        } catch (Exception $e) {
            Log::error('Application rejection failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to reject application.'], 500);
        }
    }

    /**
     * Show application details
     */
    public function show(Request $request, $id)
    {
        $application = Application::with(['property'])->findOrFail($id);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($application);
        }

        // Otherwise return view
        return view('backend.layouts.tenants.applications.show', compact('application'));
        // return false;
    }

    /**
     * Show reservation request details
     */
    public function showReservation(Request $request, $id)
    {
        $reservation = ReservationRequest::findOrFail($id);

        if ($request->ajax()) {
            return response()->json($reservation);
        }

        return abort(404);
    }

    /**
     * Update reservation request status
     */
    public function updateReservationStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,contacted,accepted,declined',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid status value.'], 422);
        }

        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->status = $request->status;
            $reservation->save();

            return response()->json([
                'message' => 'Status updated to ' . ucfirst($request->status) . ' successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Reservation status update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update status.'], 500);
        }
    }

    /**
     * Admin manually invites a tenant by email
     */
    public function sendInvitation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:tenants,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate approval token
            $token = Str::random(64);
            $applicationToken = DB::table('application_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'expires_at' => now()->addHours(24), 'created_at' => now(), 'updated_at' => now()]
            );

            // Send form link email
            $formLink = config('app.frontend_url') . "/apply-lease?" . http_build_query([
                'token' => $token,
                'email' => $request->email,
            ]);

            Mail::to($request->email)->queue(new TenantFormLinkMail($request->email, $formLink));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully to ' . $request->email,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Invitation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invitation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getInvitationData(Request $request)
    {
        $invitations = DB::table('application_tokens')->get();
        return response()->json([
            'success' => true,
            'data' => $invitations,
        ]);
    }
}
