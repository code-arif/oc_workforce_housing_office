<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\TenantApplication\TenantFormLinkMail;
use App\Models\Application;
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
                ->addColumn('applicant', function ($data){
                    
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
                ->addColumn('action', function ($data){
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
                'password'           => Hash::make($application->application_number),
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
                'address'   => $application->address,
            ]);

            TenantEmergencyContact::create([
                'tenant_id'    => $tenant->id,
                'name'         => $application->emergency_contact_name,
                'relationship' => $application->emergency_contact_relationship,
                'phone'        => $application->emergency_contact_phone,
            ]);

            TenantEmploymentHistory::create([
                'tenant_id'   => $tenant->id,
                'company_name' => $application->company_name,
                'position'     => $application->position,
                'salary'       => $application->salary,
            ]);


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

            // Generate approval token and send form link email
            $tenant->generateApprovalToken();

            try {
                $formLink = config('app.frontend_url') . "/apply-lease?" . http_build_query([
                    'token' => $tenant->approval_token,
                    'email' => $tenant->email,
                ]);

                Log::info('Tenant form link: ' . $formLink);
                Mail::to($tenant->email)->queue(new TenantFormLinkMail($tenant, $formLink));
            } catch (Exception $e) {
                Log::error('Failed to send tenant form link email: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'message'   => 'Application approved. Tenant created with under_review status and form link sent to ' . $tenant->email,
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
        $application = Application::findOrFail($id);

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json($application);
        }

        // Otherwise return view
        return view('backend.layouts.tenants.applications.show', compact('application'));
        // return false;
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

            // Check if already applied
            $existingApplication = Application::where('email', $request->email)
                ->whereNull('company_name')
                ->whereNull('reservation_item')
                ->first();

            // Create application record if not exists
            if (!$existingApplication) {
                Application::create([
                    'email'  => $request->email,
                    'status' => 'approved',
                ]);
            } else {
                $existingApplication->update(['status' => 'approved']);
            }

            // Create tenant
            $tenant = Tenant::create([
                'email'              => $request->email,
                'status'             => 'approved',
                'password'           => Hash::make(Str::random(16)),
                'application_source' => 'admin',
            ]);

            // Generate approval token
            $tenant->generateApprovalToken();
            $tenant->refresh();

            // Send form link email
            $formLink = config('app.frontend_url') . "/apply-lease?" . http_build_query([
                'token' => $tenant->approval_token,
                'email' => $tenant->email,
            ]);

            Mail::to($tenant->email)->queue(new TenantFormLinkMail($tenant, $formLink));

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
}
