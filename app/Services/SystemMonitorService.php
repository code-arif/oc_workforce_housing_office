<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\Lease;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;

class SystemMonitorService
{
    /**
     * Get complete system overview
     */
    public function getSystemOverview()
    {
        return [
            'health_status' => $this->getHealthStatus(),
            'system_resources' => $this->getSystemResources(),
            'database_info' => $this->getDatabaseInfo(),
            'application_stats' => $this->getApplicationStats(),
            'user_activity' => $this->getUserActivity(),
            'queue_status' => $this->getQueueStatus(),
            'cache_status' => $this->getCacheStatus(),
            'storage_info' => $this->getStorageInfo(),
            'recent_errors' => $this->getRecentErrors(),
            'security_overview' => $this->getSecurityOverview(),
        ];
    }

    /**
     * Get system health status
     */
    public function getHealthStatus()
    {
        $checks = [];

        // Database connectivity
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'healthy', 'message' => 'Database connected'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }

        // Cache connectivity
        try {
            Cache::put('health_check_' . time(), true, 60);
            Cache::forget('health_check_' . time());
            $checks['cache'] = ['status' => 'healthy', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'degraded', 'message' => $e->getMessage()];
        }

        // Queue connectivity
        try {
            $connection = Queue::connection();
            $checks['queue'] = ['status' => 'healthy', 'message' => 'Queue: ' . config('queue.default')];
        } catch (\Exception $e) {
            $checks['queue'] = ['status' => 'degraded', 'message' => $e->getMessage()];
        }

        // Laravel version
        $checks['laravel'] = ['status' => 'info', 'message' => 'Laravel ' . app()->version()];

        // PHP version
        $checks['php'] = ['status' => 'info', 'message' => 'PHP ' . phpversion()];

        return [
            'checks' => $checks,
            'overall_status' => $this->calculateOverallHealth($checks),
            'timestamp' => now(),
        ];
    }

    /**
     * Get system resources (CPU, Memory, Disk)
     */
    public function getSystemResources()
    {
        $resources = [];

        // Memory usage
        $memory = [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
        ];
        $memory['limit_bytes'] = $this->parseBytes($memory['limit']);
        $memory['usage_percent'] = round(($memory['used'] / $memory['limit_bytes']) * 100, 2);
        $resources['memory'] = $memory;

        // Disk usage
        if (is_readable(storage_path())) {
            $diskUsage = disk_free_space(storage_path());
            $diskTotal = disk_total_space(storage_path());
            $diskUsed = $diskTotal - $diskUsage;
            $resources['disk'] = [
                'total' => $diskTotal,
                'used' => $diskUsed,
                'free' => $diskUsage,
                'usage_percent' => round(($diskUsed / $diskTotal) * 100, 2),
                'total_formatted' => $this->formatBytes($diskTotal),
                'used_formatted' => $this->formatBytes($diskUsed),
                'free_formatted' => $this->formatBytes($diskUsage),
            ];
        }

        // CPU (if available)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $resources['cpu'] = [
                'load_average' => $load,
                'load_1min' => $load[0],
                'load_5min' => $load[1],
                'load_15min' => $load[2],
            ];
        }

        return $resources;
    }

    /**
     * Get database information
     */
    public function getDatabaseInfo()
    {
        try {
            $pdo = DB::connection()->getPdo();

            // Get table information
            $tables = DB::select("SELECT table_name, table_rows, data_length, index_length FROM information_schema.tables WHERE table_schema = ?", [env('DB_DATABASE')]);

            $totalRows = 0;
            $totalSize = 0;
            $tableList = [];

            foreach ($tables as $table) {
                $totalRows += $table->table_rows;
                $totalSize += ($table->data_length + $table->index_length);
                $tableList[] = [
                    'name' => $table->table_name,
                    'rows' => $table->table_rows,
                    'size' => $this->formatBytes($table->data_length + $table->index_length),
                ];
            }

            return [
                'name' => env('DB_DATABASE'),
                'driver' => env('DB_CONNECTION'),
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'table_count' => count($tables),
                'total_rows' => $totalRows,
                'total_size' => $this->formatBytes($totalSize),
                'total_size_bytes' => $totalSize,
                'tables' => collect($tableList)->sortByDesc('rows')->take(10)->toArray(),
                'status' => 'connected',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get application statistics
     */
    public function getApplicationStats()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::count(),
            'admin_users' => User::whereHas('roles', function ($q) { $q->whereIn('name', ['admin', 'superadmin']); })->count(),
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'pending_tenants' => Tenant::where('status', 'pending')->count(),
            'total_properties' => Property::count(),
            'active_properties' => Property::where('is_active', true)->count(),
            'total_leases' => Lease::count(),
            'active_leases' => Lease::where('status', 'ACTIVE')->count(),
            'pending_leases' => Lease::where('status', 'PENDING_TENANT_SIGN')->count(),
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'total_maintenance_requests' => MaintenanceRequest::count(),
            'open_maintenance_requests' => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
        ];
    }

    /**
     * Get user activity
     */
    public function getUserActivity()
    {
        $recentLoginDays = 30;
        // $recentLogins = DB::table('activity_log')
        //     ->where('action', 'login')
        //     ->where('created_at', '>=', now()->subDays($recentLoginDays))
        //     ->orderBy('created_at', 'desc')
        //     ->limit(10)
        //     ->get();

        // If activity log doesn't exist, try to get from authentication attempts
        // if ($recentLogins->isEmpty()) {
            $recentLogins = User::orderBy('updated_at', 'desc')
                ->limit(10)
                ->select('name', 'email', 'updated_at as last_login')
                ->get();
        // }

        return [
            'total_active_users' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'total_verified_users' => User::count(),
            'users_logged_in_today' => User::where('updated_at', '>=', now()->startOfDay())->count(),
            'recent_registrations' => User::orderBy('created_at', 'desc')->limit(5)->select('name', 'email', 'created_at')->get(),
            'recent_logins' => $recentLogins->take(5),
        ];
    }

    /**
     * Get queue status
     */
    public function getQueueStatus()
    {
        try {
            $connection = config('queue.default');

            return [
                'driver' => $connection,
                'status' => 'configured',
                'queue' => config('queue.connections.' . $connection . '.queue') ?? 'default',
            ];
        } catch (\Exception $e) {
            return [
                'driver' => config('queue.default'),
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache status
     */
    public function getCacheStatus()
    {
        try {
            $driver = config('cache.default');
            $stores = config('cache.stores');
            $storeConfig = $stores[$driver] ?? [];

            return [
                'driver' => $driver,
                'status' => 'operational',
                'config' => [
                    'host' => $storeConfig['host'] ?? 'N/A',
                    'port' => $storeConfig['port'] ?? 'N/A',
                    'database' => $storeConfig['database'] ?? 'N/A',
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage information
     */
    public function getStorageInfo()
    {
        $storagePath = storage_path('app');
        $publicPath = public_path('uploads');

        return [
            'app_storage' => [
                'size' => $this->getDirectorySize($storagePath),
                'size_formatted' => $this->formatBytes($this->getDirectorySize($storagePath)),
                'path' => $storagePath,
            ],
            'public_uploads' => [
                'size' => $this->getDirectorySize($publicPath),
                'size_formatted' => $this->formatBytes($this->getDirectorySize($publicPath)),
                'path' => $publicPath,
            ],
            'logs' => [
                'size' => $this->getDirectorySize(storage_path('logs')),
                'size_formatted' => $this->formatBytes($this->getDirectorySize(storage_path('logs'))),
                'path' => storage_path('logs'),
            ],
        ];
    }

    /**
     * Get recent errors from logs
     */
    public function getRecentErrors()
    {
        $logPath = storage_path('logs/laravel.log');
        $errors = [];

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            // Split by new lines and filter for ERROR, EXCEPTION, CRITICAL
            $lines = array_reverse(explode(PHP_EOL, $content));

            foreach ($lines as $line) {
                if (preg_match('/\[(ERROR|EXCEPTION|CRITICAL|WARNING)\]/', $line) && count($errors) < 10) {
                    $errors[] = [
                        'message' => substr($line, 0, 200),
                        'timestamp' => $this->extractTimestampFromLog($line),
                    ];
                }
            }
        }

        return [
            'total_errors' => count($errors),
            'recent_errors' => array_slice($errors, 0, 5),
            // 'log_file_size' => $this->formatBytes(filesize($logPath) ?? 0),
        ];
    }

    /**
     * Get security overview
     */
    public function getSecurityOverview()
    {
        return [
            'https_enabled' => env('APP_URL') !== null && strpos(env('APP_URL'), 'https://') === 0,
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
            'latest_password_changes' => User::orderBy('updated_at', 'desc')
                ->limit(5)
                ->select('name', 'email', 'updated_at')
                ->get(),
            'superadmin_count' => User::whereHas('roles', function ($q) { $q->where('name', 'superadmin'); })->count(),
            'admin_count' => User::whereHas('roles', function ($q) { $q->where('name', 'admin'); })->count(),
            'suspended_users' => User::count(),
        ];
    }

    /**
     * Helper Functions
     */

    private function calculateOverallHealth($checks)
    {
        $unhealthyCount = 0;
        $degradedCount = 0;

        foreach ($checks as $check) {
            if (isset($check['status'])) {
                if ($check['status'] === 'unhealthy') $unhealthyCount++;
                if ($check['status'] === 'degraded') $degradedCount++;
            }
        }

        if ($unhealthyCount > 0) return 'unhealthy';
        if ($degradedCount > 0) return 'degraded';
        return 'healthy';
    }

    private function parseBytes($value)
    {
        $value = trim($value);
        if (is_numeric($value)) {
            return (int)$value;
        }

        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function getDirectorySize($path)
    {
        $size = 0;

        if (!is_dir($path) || !is_readable($path)) {
            return 0;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function extractTimestampFromLog($line)
    {
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches);
        return $matches[1] ?? 'Unknown';
    }
}
