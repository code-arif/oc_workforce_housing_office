<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Billable;

    // fillable
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'address',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
    ];

    protected $hidden = [
        'remember_token',
        'google_access_token',
        'google_refresh_token',
    ];

    protected $casts = [
        'google_token_expires_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function team()
    {
        return $this->hasOne(TeamUser::class, 'user_id', 'id')->with('team');
    }

    public function teamUser()
    {
        return $this->hasOne(User::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users', 'user_id', 'team_id');
    }
}
