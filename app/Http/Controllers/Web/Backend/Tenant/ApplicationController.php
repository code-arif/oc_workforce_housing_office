<?php

namespace App\Http\Controllers\Web\Backend\Tenant;

use Exception;
use App\Models\Tenant;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;
use App\Mail\TenantApplication\TenantFormLinkMail;

class ApplicationController extends Controller
{

    /**
     * Application list
     */
    public function index()
    {
        // Get statistics for both types
        $singleEmailTotal = Application::whereNotNull('email')
            ->whereNull('company_name')
            ->whereNull('reservation_item')
            ->count();

        $singleEmailPending = Application::whereNotNull('email')
            ->whereNull('company_name')
            ->whereNull('reservation_item')
            ->where('status', 'pending')
            ->count();

        $singleEmailApproved = Application::whereNotNull('email')
            ->whereNull('company_name')
            ->whereNull('reservation_item')
            ->where('status', 'approved')
            ->count();

        $reservationTotal = Application::whereNotNull('company_name')
            ->orWhereNotNull('reservation_item')
            ->count();

        $reservationPending = Application::where(function ($q) {
            $q->whereNotNull('company_name')
                ->orWhereNotNull('reservation_item');
        })
            ->where('status', 'pending')
            ->count();

        $reservationApproved = Application::where(function ($q) {
            $q->whereNotNull('company_name')
                ->orWhereNotNull('reservation_item');
        })
            ->where('status', 'approved')
            ->count();

        return view('backend.layouts.tenants.applications.index', compact(
            'singleEmailTotal',
            'singleEmailPending',
            'singleEmailApproved',
            'reservationTotal',
            'reservationPending',
            'reservationApproved'
        ));
    }

    /**
     * Application list data
     */
    public function getData(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $type = $request->get('type', 'single'); // 'single' or 'reservation'

            $query = Application::query()
                ->select([
                    'applications.*'
                ])
                ->orderBy('applications.id', 'desc');

            // Filter by type
            if ($type === 'single') {
                $query->whereNotNull('email')
                    ->whereNull('company_name')
                    ->whereNull('reservation_item');
            } else {
                $query->where(function ($q) {
                    $q->whereNotNull('company_name')
                        ->orWhereNotNull('reservation_item');
                });
            }

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
                ->addColumn('applicant', function ($data) use ($type) {
                    if ($type === 'single') {
                        return '<div class="text-truncate">
                                    <div class="fw-semibold">' . e($data->email) . '</div>
                                    <small class="text-muted">' . ($data->first_name ? e($data->first_name . ' ' . $data->last_name) : 'Name not provided') . '</small>
                                </div>';
                    } else {
                        $name = trim(($data->first_name ?? '') . ' ' . ($data->last_name ?? ''));
                        return '<div class="text-truncate">
                                    <div class="fw-semibold">' . e($data->company_name ?? 'N/A') . '</div>
                                    <small class="text-muted">' . e($name ?: $data->email) . '</small>
                                </div>';
                    }
                })
                ->addColumn('contact', function ($data) {
                    return '<div class="text-truncate">
                                <div>' . e($data->email) . '</div>
                                <small class="text-muted">' . e($data->phone ?? 'No phone') . '</small>
                            </div>';
                })
                ->addColumn('details', function ($data) use ($type) {
                    if ($type === 'single') {
                        return '<span class="text-muted">Email-only application</span>';
                    } else {
                        $items = $data->reservation_item ?? [];
                        $count = is_array($items) ? count($items) : 0;
                        return '<div class="text-truncate">
                                    <div class="fw-semibold">' . e($data->industry ?? 'N/A') . '</div>
                                    <small class="text-muted">' . $count . ' reservation item(s)</small>
                                </div>';
                    }
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
                ->addColumn('action', function ($data) use ($type) {
                    $btn = '<div class="btn-group" role="group">';

                    if ($data->status === 'pending') {
                        if ($type === 'single') {
                            // Single email - only approve button
                            $btn .= '<button type="button" onclick="approveSingleEmail(' . $data->id . ')" class="btn btn-sm btn-success d-inline-flex align-items-center" title="Approve & Send Form">
                                        <i class="fe fe-check"></i>
                                    </button>';


                            $btn .= '<button type="button" onclick="rejectApplication(' . $data->id . ')" class="btn btn-sm btn-danger d-inline-flex align-items-center" title="Reject">
                                    <i class="fe fe-x"></i>
                                </button>';
                        } else {
                            // Reservation - approve and reject
                            $btn .= '<button type="button" onclick="seeReservation(' . $data->id . ')" class="btn btn-sm btn-success d-inline-flex align-items-center" title="Approve">
                                        <i class="fe fe-eye"></i>
                                    </button>';
                        }
                    } else {
                        $btn .= '<span class="badge p-3 bg-secondary">Already processed</span>';
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
     * Approve single email application
     * Creates dummy tenant and sends form link
     */
    public function approveSingleEmail($id)
    {
        try {
            DB::beginTransaction();

            $application = Application::findOrFail($id);

            // Validate it's a single email application
            if ($application->company_name || $application->reservation_item) {
                return response()->json(['message' => 'This is not a single email application.'], 400);
            }

            if ($application->status !== 'pending') {
                return response()->json(['message' => 'This application has already been processed.'], 400);
            }

            // Check if tenant already exists
            $existingTenant = Tenant::where('email', $application->email)->first();
            if ($existingTenant) {
                return response()->json(['message' => 'A tenant with this email already exists.'], 400);
            }

            // Create dummy tenant
            $tenant = Tenant::create([
                'email' => $application->email,
                'status' => 'approved',
                'password' => Hash::make(Str::random(16)), // temporary password
            ]);

            // Update application status
            $application->status = 'approved';
            $application->save();

            // auto generate approval token
            $tenant->generateApprovalToken();

            // Send email with form link
            try {
                // $formLink = config('app.frontend_url') . "/apply-lease & token ={$tenant->approval_token}";
                $formLink = config('app.frontend_url') . "/apply-lease?" . http_build_query([
                    'token' => $tenant->approval_token,
                    'email' => $tenant->email,
                ]);

                Log::info($formLink); // check form link
                Mail::to($tenant->email)->send(new TenantFormLinkMail($tenant, $formLink));
            } catch (Exception $e) {
                Log::error('Failed to send tenant form link email: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Application approved successfully. Tenant form link has been sent to ' . $tenant->email,
                'tenant_id' => $tenant->id
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
}
