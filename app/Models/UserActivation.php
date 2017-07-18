<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'token', 'expired_at', 'created_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    public $timestamps = false;
}
