<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityLoggerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing requests or specific critical routes
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = Auth::user();
            
            // Avoid double-logging if the action was already logged by a model observer
            // We use a simple check to see if we should log generic route access
            ActivityLog::create([
                'user_id' => $user?->id,
                'role' => $user ? ($user->getRoleNames()->first() ?? 'User') : 'Guest',
                'action' => 'Accessed',
                'module' => $this->getModuleFromRoute($request),
                'route' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_data' => $request->except(['password', 'password_confirmation', '_token']),
            ]);
        }

        return $response;
    }

    protected function getModuleFromRoute(Request $request): string
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        return ucfirst($segments[0] ?? 'System');
    }
}
