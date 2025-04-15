<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'score', 'value', 'firstvalue',
    ];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
