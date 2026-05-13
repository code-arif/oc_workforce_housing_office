<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LoggableActivity
{
    /**
     * Boot the trait and register observers
     */
    protected static function bootLoggableActivity()
    {
        static::created(function (Model $model) {
            static::logActivity($model, 'Created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldData = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newData = $model->getChanges();
            
            // Don't log if only timestamps changed
            unset($oldData['updated_at'], $newData['updated_at']);
            
            if (!empty($newData)) {
                static::logActivity($model, 'Updated', $oldData, $newData);
            }
        });

        static::deleted(function (Model $model) {
            static::logActivity($model, 'Deleted', $model->getAttributes(), null);
        });
    }

    /**
     * Helper to log the activity
     */
    protected static function logActivity(Model $model, string $action, ?array $oldData, ?array $newData)
    {
        $user = Auth::user();
        $module = str_replace('App\\Models\\', '', get_class($model));

        ActivityLog::create([
            'user_id' => $user?->id,
            'role' => $user ? ($user->getRoleNames()->first() ?? 'User') : 'Guest',
            'action' => $action,
            'module' => $module,
            'route' => Request::fullUrl(),
            'method' => Request::method(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'subject_type' => get_class($model),
            'subject_id' => $model->id,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }
}
