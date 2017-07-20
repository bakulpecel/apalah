<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'start_at', 'end_at',
    ];

    public $timestamps = true;
}
