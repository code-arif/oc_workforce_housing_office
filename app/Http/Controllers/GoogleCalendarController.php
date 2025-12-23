<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\Work;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;

class GoogleCalendarController extends Controller
{
    protected $googleCalendar;

    // serivce injection
    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    // show calendar view
    public function index(Request $request)
    {
        $teams = Team::all();
        $categories = Category::all();
        $user = Auth::user();

        $isGoogleConnected = !empty($user->google_access_token);

        return view('backend.layouts.calendar.index', compact('teams', 'categories', 'isGoogleConnected'));
    }

    // redirect to google for auth
    public function redirectToGoogle()
    {
        try {
            $authUrl = $this->googleCalendar->getAuthUrl();
            return redirect()->away($authUrl);
        } catch (Exception $e) {
            Log::error('Google Redirect Error', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('calendar.index')
                ->with('error', 'Failed to connect to Google Calendar');
        }
    }

    // handle google callback after auth
    public function handleGoogleCallback(Request $request)
    {
        try {
            if ($request->has('error')) {
                Log::warning('Google authentication cancelled by user', [
                    'error' => $request->get('error')
                ]);

                return redirect()->route('calendar.index')
                    ->with('error', 'Google authentication cancelled');
            }

            if (!$request->has('code')) {
                Log::error('No authorization code received from Google');

                return redirect()->route('calendar.index')
                    ->with('error', 'No authorization code received');
            }

            $code = $request->get('code');

            Log::info('Processing Google OAuth callback', [
                'code_length' => strlen($code)
            ]);

            // Get token from Google
            $token = $this->googleCalendar->authenticate($code);

            if (!isset($token['access_token'])) {
                Log::error('No access token in response', [
                    'token_keys' => array_keys($token)
                ]);

                return redirect()->route('calendar.index')
                    ->with('error', 'Failed to receive access token');
            }

            $user = Auth::user();

            // CRITICAL: Preserve existing refresh token if new one not provided
            $existingRefreshToken = $user->google_refresh_token;

            // Save new access token (always as JSON)
            $user->google_access_token = json_encode($token);

            // Handle refresh token carefully
            if (isset($token['refresh_token'])) {
                // New refresh token provided (first time or re-authorized)
                $user->google_refresh_token = $token['refresh_token'];
                Log::info('New refresh token received and saved');
            } elseif ($existingRefreshToken) {
                // No new refresh token, keep the old one
                Log::info('Preserving existing refresh token');
                // No need to update, it's already there
            } else {
                // No refresh token at all - this is a problem
                Log::error('No refresh token available', [
                    'has_token_refresh' => isset($token['refresh_token']),
                    'has_existing_refresh' => !empty($existingRefreshToken)
                ]);

                return redirect()->route('calendar.index')
                    ->with('error', 'No refresh token received. Please try disconnecting and reconnecting.');
            }

            // Set token expiry time
            if (isset($token['expires_in'])) {
                $user->google_token_expires_at = Carbon::now()->addSeconds($token['expires_in']);
            } else {
                // Default to 1 hour if not provided
                $user->google_token_expires_at = Carbon::now()->addHour();
            }

            $user->save();

            Log::info('Google Calendar connected successfully', [
                'user_id' => $user->id,
                'has_refresh_token' => !empty($user->google_refresh_token),
                'token_expires_at' => $user->google_token_expires_at,
                'expires_in_minutes' => Carbon::now()->diffInMinutes($user->google_token_expires_at)
            ]);

            return redirect()->route('calendar.index')
                ->with('success', 'Google Calendar connected successfully!');
        } catch (Exception $e) {
            Log::error('Google Callback Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('calendar.index')
                ->with('error', 'Failed to connect Google Calendar: ' . $e->getMessage());
        }
    }

    // disconnet google calendar
    public function disconnect()
    {
        try {
            $user = Auth::user();
            $user->google_access_token = null;
            $user->google_refresh_token = null;
            $user->google_token_expires_at = null;
            $user->save();

            Work::whereNotNull('google_event_id')->update(['google_event_id' => null]);

            return redirect()->route('calendar.index')
                ->with('success', 'Google Calendar disconnected successfully!');
        } catch (Exception $e) {
            Log::error('Google Disconnect Error', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('calendar.index')
                ->with('error', 'Failed to disconnect Google Calendar');
        }
    }
}
