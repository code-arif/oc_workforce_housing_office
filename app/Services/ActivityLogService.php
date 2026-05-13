<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log a custom activity
     */
    public function log(string $action, string $module, $subject = null, ?array $oldData = null, ?array $newData = null): ActivityLog
    {
        $user = Auth::user();
        
        return ActivityLog::create([
            'user_id' => $user?->id,
            'role' => $user ? $user->getRoleNames()->first() ?? 'User' : 'Guest',
            'action' => $action,
            'module' => $module,
            'route' => Request::fullUrl(),
            'method' => Request::method(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}
