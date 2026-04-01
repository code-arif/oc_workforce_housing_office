<?php

namespace App\Http\Controllers\Web\Backend;

use App\Services\SystemMonitorService;
use App\Http\Controllers\Controller;

class SystemMonitorController extends Controller
{
    protected $monitorService;

    public function __construct(SystemMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
        // Restrict to superadmin only
        // $this->middleware('role:superadmin');
    }

    /**
     * Display the system monitoring dashboard
     */
    public function index()
    {
        $overview = $this->monitorService->getSystemOverview();

        return view('backend.layouts.system-monitor.index', [
            'overview' => $overview,
            'pageTitle' => 'System Monitor',
        ]);
    }

    /**
     * Get system health status
     */
    public function health()
    {
        $health = $this->monitorService->getHealthStatus();

        return view('backend.layouts.system-monitor.health', [
            'health' => $health,
        ]);
    }

    /**
     * Get system resources
     */
    public function resources()
    {
        $resources = $this->monitorService->getSystemResources();

        return view('backend.layouts.system-monitor.resources', [
            'resources' => $resources,
        ]);
    }

    /**
     * Get database information
     */
    public function database()
    {
        $database = $this->monitorService->getDatabaseInfo();

        return view('backend.layouts.system-monitor.database', [
            'database' => $database,
        ]);
    }

    /**
     * Get application statistics
     */
    public function applications()
    {
        $stats = $this->monitorService->getApplicationStats();

        return view('backend.layouts.system-monitor.applications', [
            'stats' => $stats,
        ]);
    }

    /**
     * Get user activity
     */
    public function activity()
    {
        $activity = $this->monitorService->getUserActivity();

        return view('backend.layouts.system-monitor.activity', [
            'activity' => $activity,
        ]);
    }

    /**
     * Get security overview
     */
    public function security()
    {
        $security = $this->monitorService->getSecurityOverview();

        return view('backend.layouts.system-monitor.security', [
            'security' => $security,
        ]);
    }

    /**
     * Get error logs
     */
    public function errors()
    {
        $errors = $this->monitorService->getRecentErrors();

        return view('backend.layouts.system-monitor.errors', [
            'errors' => $errors,
        ]);
    }

    /**
     * API: Get complete system overview (JSON)
     */
    public function getOverview()
    {
        try {
            $overview = $this->monitorService->getSystemOverview();

            return response()->json([
                'success' => true,
                'data' => $overview,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get health status (JSON)
     */
    public function getHealthStatus()
    {
        try {
            $health = $this->monitorService->getHealthStatus();

            return response()->json([
                'success' => true,
                'data' => $health,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get system resources (JSON)
     */
    public function getResources()
    {
        try {
            $resources = $this->monitorService->getSystemResources();

            return response()->json([
                'success' => true,
                'data' => $resources,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get database info (JSON)
     */
    public function getDatabaseInfo()
    {
        try {
            $database = $this->monitorService->getDatabaseInfo();

            return response()->json([
                'success' => true,
                'data' => $database,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get application stats (JSON)
     */
    public function getApplicationStats()
    {
        try {
            $stats = $this->monitorService->getApplicationStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get user activity (JSON)
     */
    public function getUserActivity()
    {
        try {
            $activity = $this->monitorService->getUserActivity();

            return response()->json([
                'success' => true,
                'data' => $activity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
