<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_users', 'team_id', 'user_id');
    }

    public function teamUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A team has many works
     */
    public function works()
    {
        return $this->hasMany(Work::class, 'team_id');
    }

    public function locations()
    {
        return $this->hasMany(TeamLocation::class);
    }

    // Get team leader
    public function leader()
    {
        return $this->belongsToMany(User::class, 'team_users')
            ->wherePivot('is_leader', true)
            ->first();
    }
}
