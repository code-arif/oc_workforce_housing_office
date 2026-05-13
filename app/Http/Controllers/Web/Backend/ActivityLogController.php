<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Display the activity log list page
     */
    public function index()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        $modules = ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('backend.layouts.activity-logs.index', compact('users', 'modules', 'actions'));
    }

    /**
     * Get DataTable data
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::with('user:id,name')->select('activity_logs.*');

            // Filtering
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('search_value')) {
                $search = $request->search_value;
                $query->where(function ($q) use ($search) {
                    $q->where('route', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="form-check-input log-checkbox m-0" value="' . $row->id . '">';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('M d, Y H:i:s');
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user ? e($row->user->name) : '<span class="text-muted">Guest</span>';
                })
                ->addColumn('details', function ($row) {
                    return '
                    <div class="btn-group">
                        <button type="button"
                            onclick="viewLogDetails(' . $row->id . ')"
                            class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <i class="fe fe-info"></i>
                            <span>Details</span>
                        </button>
                        <button type="button"
                            onclick="deleteLog(' . $row->id . ')"
                            class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                            <i class="fe fe-trash-2"></i>
                        </button>
                    </div>
                ';
                })

                ->addColumn('action_link', function ($row) {

                    $url = $this->getRedirectUrl($row);

                    if ($url) {
                        return '
                    <a href="' . $url . '"
                    class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
                        <i class="fe fe-link"></i>
                        <span>Go to Entity</span>
                    </a>
                ';
                    }

                    return '<span class="text-muted">N/A</span>';
                })
                ->rawColumns(['checkbox', 'user_name', 'details', 'action_link'])
                ->make(true);
        }
    }

    /**
     * Get single log details
     */
    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'log' => $log,
                'redirect_url' => $this->getRedirectUrl($log)
            ]
        ]);
    }

    /**
     * Helper to get redirect URL based on subject type
     */
    protected function getRedirectUrl($log)
    {
        if (!$log->subject_type || !$log->subject_id) {
            return null;
        }

        try {
            switch ($log->subject_type) {
                case 'App\Models\Tenant':
                    return route('tenants.show', $log->subject_id);
                case 'App\Models\Lease':
                    return route('leases.show', $log->subject_id);
                case 'App\Models\Invoice':
                    return route('invoices.show', $log->subject_id);
                case 'App\Models\Payment':
                    return route('tenants.payments.details', $log->subject_id);
                case 'App\Models\MaintenanceRequest':
                    return route('maintanance.show', $log->subject_id);
                case 'App\Models\User':
                    return route('user-management.users.show', $log->subject_id);
                case 'App\Models\Property':
                    return route('property.show', $log->subject_id);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Export logs to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['user_id', 'module', 'action', 'date_from', 'date_to']);
        $format = $request->get('format', 'xlsx');
        $filename = 'activity_logs_' . now()->format('Y_m_d_His') . '.' . $format;

        return Excel::download(new \App\Exports\ActivityLogsExport($filters), $filename);
    }

    /**
     * Delete a single log
     */
    public function destroy($id)
    {
        try {
            ActivityLog::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Activity log deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete activity log.']);
        }
    }

    /**
     * Bulk delete logs
     */
    public function massDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:activity_logs,id'
        ]);

        try {
            ActivityLog::whereIn('id', $request->ids)->delete();
            return response()->json(['success' => true, 'message' => 'Selected activity logs deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete selected activity logs.']);
        }
    }
}
