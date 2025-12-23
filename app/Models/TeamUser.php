<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'is_leader',
        'is_tracking_active',
    ];

    protected $table = 'team_users';

    /**
     * Relation to the User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relation to the Team model
     */
    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
