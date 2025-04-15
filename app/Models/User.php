<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use \Awobaz\Compoships\Compoships;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'knsb_id', 'rating', 'beschikbaar', 'settings', 'active', 'activate', 'rechten',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'json',
        'password' => 'hashed',
    ];

    /**
     * Get the user settings.
     *
     * @return Settings
     */
    public function settings()
    {
        return new Settings($this->settings, $this);
    }

    public function settingsGet(User $user_to_get)
    {
        return new Settings($user_to_get->settings, $user_to_get);
    }

    public function ranking()
    {
        return $this->belongsTo('App\Models\Ranking');
    }

    public function presences()
    {
        return $this->hasMany('App\Models\Presence');
    }

    public function games()
    {
        return $this->hasMany('App\Models\Game', ['white', 'black']);
    }

    public function scopeWithSetting($query, $setting, $value = true)
    {
        return $query->where('settings->'.str_replace('.', '->', $setting), $value);
    }
}
