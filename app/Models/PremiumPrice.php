<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumPrice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'month', 'price',
    ];

    public $timestamps = true;
}
