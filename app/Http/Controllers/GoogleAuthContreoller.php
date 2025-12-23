<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthContreoller extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->scopes([
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        ])->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Store Google tokens in session or database
            session([
                'google_access_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);

            return redirect()->route('calendar.index')->with('success', 'Google Calendar connected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('calendar.index')->with('error', 'Failed to connect Google Calendar: ' . $e->getMessage());
        }
    }

    public function disconnectGoogle()
    {
        session()->forget(['google_access_token', 'google_refresh_token']);
        return redirect()->route('calendar.index')->with('success', 'Google Calendar disconnected.');
    }
}
