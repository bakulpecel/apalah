<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\UserActivation;

class UserActivationRepository extends Repository
{
    public static function create(array $data)
    {
        return UserActivation::create([
            'email'      => $data['email'],
            'token'      => str_random(60),
            'expired_at' => Carbon::now('Asia/Jakarta')->addDay(1),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);
    }
}