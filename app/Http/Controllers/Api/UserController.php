<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($username)
    {
        $username = substr($username, 1);
        
        $user = User::where('username', $username)
            ->where('active', 1)
            ->first();

        if (!$user) {
            return $this->resJsonError('Pengguna tidak ditemukan!.', 404);
        }

        $response = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }
}
