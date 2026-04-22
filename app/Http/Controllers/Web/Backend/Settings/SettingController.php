<?php

namespace App\Http\Controllers\Web\Backend\Settings;


use Exception;
use App\Helper\Helper;
use App\Models\Setting;
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

            return back()->with('t-success', 'Mail settings updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update mail settings: ' . $e->getMessage());
        }
    }
}
