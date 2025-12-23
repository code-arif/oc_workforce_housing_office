<?php

use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Location Tracking Channels
 * Public channel - Anyone can listen to team locations
 */
Broadcast::channel('team-location.{teamId}', function ($user, $teamId) {
    // Option 1: Public channel (anyone can listen)
    // return true;

    // Option 2: Only admin can listen (recommended for admin dashboard)
    if ($user->role === 'admin') {
        return true;
    }

    // Option 3: Team members can listen to their own team
    $isMember = DB::table('team_users')
        ->where('team_id', $teamId)
        ->where('user_id', $user->id)
        ->exists();

    return $isMember;
});

/**
 * Private channel - User's personal notifications
 */
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

/**
 * Presence channel - Who's online in a team
 * (Optional - for future features)
 */
Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    $teamUser = DB::table('team_users')
        ->where('team_id', $teamId)
        ->where('user_id', $user->id)
        ->first();

    if ($teamUser) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'is_leader' => $teamUser->is_leader
        ];
    }

    return false;
});

/**
 * Admin-only channel - All locations
 */
Broadcast::channel('admin-tracking', function ($user) {
    return $user->role === 'admin' ? [
        'id' => $user->id,
        'name' => $user->name
    ] : false;
});

/**
 * Work status updates channel
 */
Broadcast::channel('work.{workId}', function ($user, $workId) {
    // Check if user's team is assigned to this work
    $work = \App\Models\Work::find($workId);

    if (!$work) return false;

    $isMember = DB::table('team_users')
        ->where('team_id', $work->team_id)
        ->where('user_id', $user->id)
        ->exists();

    return $isMember || $user->role === 'admin';
});
