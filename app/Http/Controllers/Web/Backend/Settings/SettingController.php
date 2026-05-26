<?php

namespace App\Http\Controllers\Web\Backend\Settings;


use Exception;
use App\Helper\Helper;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Display the system settings page.
     *
     * @return View
     */
    public function index(): View
    {
        $setting = Setting::latest('id')->first();
        return view('backend.layouts.settings.general_settings', compact('setting'));
    }

    /**
     * Update the system settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name'           => 'nullable',
            'title'          => 'nullable',
            'description'    => 'nullable',
            'phone'          => 'nullable',
            'email'          => 'nullable',
            'copyright'      => 'nullable',
            'keywords'       => 'nullable',
            'author'         => 'nullable',
            'address'        => 'nullable',
            'logo'           => 'nullable',
            'favicon'        => 'nullable',
        ]);

        try {
            $setting = Setting::first();
            if ($request->hasFile('logo')) {
                if ($setting && $setting->logo && file_exists(public_path($setting->logo))) {
                    Helper::deleteImage(public_path($setting->logo));
                }
                // $validatedData['logo'] = Helper::uploadImage($request->file('logo'), 'settings', time() . '_' . Helper::getFileName($request->file('logo')));
                $validatedData['logo']  = Helper::uploadImage($request->logo, 'settings');
            }
            if ($request->hasFile('favicon')) {
                if ($setting && $setting->favicon && file_exists(public_path($setting->favicon))) {
                    Helper::deleteImage(public_path($setting->favicon));
                }
                // $validatedData['favicon'] = Helper::uploadImage($request->file('favicon'), 'settings', time() . '_' . Helper::getFileName($request->file('favicon')));
                $validatedData['favicon']  = Helper::uploadImage($request->favicon, 'settings');
            }

            Setting::updateOrCreate(
                [
                    'id' => 1
                ],
                $validatedData
            );
            return back()->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update' . $e->getMessage());
        }
    }

    /**
     * Display the mail settings page.
     */
    public function mailIndex(): View
    {
        $setting = Setting::latest('id')->first();
        return view('backend.layouts.settings.mail_settings', compact('setting'));
    }

    /**
     * Update mail settings.
     */
    public function mailUpdate(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'mail_mailer'       => 'nullable|in:smtp,sendmail,log',
            'mail_host'         => 'nullable|string|max:255',
            'mail_port'         => 'nullable|integer|min:1|max:65535',
            'mail_username'     => 'nullable|string|max:255',
            'mail_password'     => 'nullable|string|max:1000',
            'mail_encryption'   => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name'    => 'nullable|string|max:255',
        ]);

        try {
            $setting = Setting::first();

            if (!$request->filled('mail_password') && $setting) {
                unset($validatedData['mail_password']);
            }

            Setting::updateOrCreate(
                [
                    'id' => 1
                ],
                $validatedData
            );

            $this->updateEnvironmentFile($validatedData);
            Artisan::call('config:clear');

            return back()->with('t-success', 'Mail settings updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update mail settings: ' . $e->getMessage());
        }
    }

    /**
     * Sync mail settings to the environment file.
     */
    private function updateEnvironmentFile(array $values): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath) || !is_writable($envPath)) {
            return;
        }

        $envValues = [
            'MAIL_MAILER' => $values['mail_mailer'] ?? null,
            'MAIL_HOST' => $values['mail_host'] ?? null,
            'MAIL_PORT' => $values['mail_port'] ?? null,
            'MAIL_USERNAME' => $values['mail_username'] ?? null,
            'MAIL_PASSWORD' => $values['mail_password'] ?? null,
            'MAIL_ENCRYPTION' => $values['mail_encryption'] ?? null,
            // 'MAIL_SCHEME' => $values['mail_encryption'] ?? null,
            'MAIL_FROM_ADDRESS' => $values['mail_from_address'] ?? null,
            // 'MAIL_FROM_NAME' => $values['mail_from_name'] ?? null,
        ];

        $content = file_get_contents($envPath);

        foreach ($envValues as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $formattedValue = $this->formatEnvValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $key . '=' . $formattedValue, $content);
            } else {
                $content .= PHP_EOL . $key . '=' . $formattedValue;
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Format env values safely for the .env file.
     */
    private function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s|#|"|\'/u', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    /**
     * Update environment variables directly.
     */
    private function updateEnvVariables(array $envValues): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath) || !is_writable($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        foreach ($envValues as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $formattedValue = $this->formatEnvValue($value);
            // Allow optional spaces around the = sign when matching
            $pattern = '/^' . preg_quote($key, '/') . '\s*=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $key . '=' . $formattedValue, $content);
            } else {
                $content .= PHP_EOL . $key . '=' . $formattedValue;
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Display the Stripe settings page.
     */
    public function stripeIndex(): View
    {
        $setting = Setting::latest('id')->first();
        return view('backend.layouts.settings.stripe_settings', compact('setting'));
    }

    /**
     * Update Stripe settings.
     */
    public function stripeUpdate(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
            'stripe_ach_fee' => 'nullable|numeric|min:0',
            'stripe_card_fee_percentage' => 'nullable|numeric|min:0',
            'stripe_card_fee_fixed' => 'nullable|numeric|min:0',
        ]);

        try {
            $envValues = [
                'STRIPE_KEY' => $validatedData['stripe_key'] ?? null,
                'STRIPE_SECRET' => $validatedData['stripe_secret'] ?? null,
                'STRIPE_WEBHOOK_SECRET' => $validatedData['stripe_webhook_secret'] ?? null,
                'STRIPE_ACH_FEE' => $validatedData['stripe_ach_fee'] ?? null,
                'STRIPE_CARD_FEE_PERCENTAGE' => $validatedData['stripe_card_fee_percentage'] ?? null,
                'STRIPE_CARD_FEE_FIXED' => $validatedData['stripe_card_fee_fixed'] ?? null,
            ];

            // Persist to .env
            $this->updateEnvVariables($envValues);

            // Also persist to settings table so UI reads updated values immediately
            Setting::updateOrCreate(
                ['id' => 1],
                [
                    'stripe_key' => $validatedData['stripe_key'] ?? null,
                    'stripe_secret' => $validatedData['stripe_secret'] ?? null,
                    'stripe_webhook_secret' => $validatedData['stripe_webhook_secret'] ?? null,
                    'stripe_ach_fee' => $validatedData['stripe_ach_fee'] ?? null,
                    'stripe_card_fee_percentage' => $validatedData['stripe_card_fee_percentage'] ?? null,
                    'stripe_card_fee_fixed' => $validatedData['stripe_card_fee_fixed'] ?? null,
                ]
            );

            // Clear caches so new values are picked up immediately
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');

            return back()->with('t-success', 'Stripe settings updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update Stripe settings: ' . $e->getMessage());
        }
    }
}
