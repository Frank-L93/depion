<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'round', 'date', 'published', 'ranking',
    ];
    // Table Name
    protected $table = 'rounds';

    // Timestamps
    public $timestamps = true;

    // Relation with User
    public function presences()
    {
        return $this->hasMany('App\Models\Presence');
    }
    public function games()
    {
        return $this->hasMany('App\Models\Game');
    }
}
