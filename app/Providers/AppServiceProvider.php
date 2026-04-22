<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
