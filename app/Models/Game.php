<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo('App\Models\User', ['white', 'black'], 'id');
    }

    // Relation with Round
    public function rounds()
    {
        return $this->belongsTo('App\Models\Round');
    }
}
