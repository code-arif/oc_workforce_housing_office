<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // $this->app->singleton(GoogleCalendarService::class, function ($app) {
        //     return new GoogleCalendarService();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function ($event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'role' => $event->user->getRoleNames()->first() ?? 'User',
                'action' => 'Login',
                'module' => 'Auth',
                'route' => Request::fullUrl(),
                'method' => Request::method(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'role' => $event->user->getRoleNames()->first() ?? 'User',
                    'action' => 'Logout',
                    'module' => 'Auth',
                    'route' => Request::fullUrl(),
                    'method' => Request::method(),
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::userAgent(),
                ]);
            }
        });

        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            $setting = Setting::query()->first();

            if (!$setting) {
                return;
            }

            // config([
            //     'mail.default' => $setting->mail_mailer ?: config('mail.default'),
            //     'mail.mailers.smtp.host' => $setting->mail_host ?: config('mail.mailers.smtp.host'),
            //     'mail.mailers.smtp.port' => $setting->mail_port ?: config('mail.mailers.smtp.port'),
            //     'mail.mailers.smtp.username' => $setting->mail_username ?: config('mail.mailers.smtp.username'),
            //     'mail.mailers.smtp.password' => $setting->mail_password ?: config('mail.mailers.smtp.password'),
            //     'mail.mailers.smtp.scheme' => $setting->mail_mailer ?: config('mail.mailers.smtp.scheme'),
            //     'mail.from.address' => $setting->mail_from_address ?: config('mail.from.address'),
            //     // 'mail.from.name' => $setting->mail_from_name ?: config('mail.from.name'),
            // ]);
        } catch (\Throwable $e) {
            // Keep app boot resilient if settings table is not available yet.
        }
    }
}
