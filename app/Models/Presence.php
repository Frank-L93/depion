<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    // Table Name
    protected $table = 'presences';

    // Timestamps
    public $timestamps = true;

    // Relation with User
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // Relation with Round
    public function rounds()
    {
        return $this->hasMany('App\Models\Round');
    }
}
