<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

// Not really a Helper
class TPRHelper extends Model
{
    // Table Name
    protected $table = 'tprhelper';

    protected $fillable = ['p', 'dp'];

    // Timestamps
    public $timestamps = true;
}
