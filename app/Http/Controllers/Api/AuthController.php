<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Repositories\UserActivationRepository;
use App\Repositories\UserRepository;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:20',
            'username' => 'required|min:5|max:15|alpha_num|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        $user = UserRepository::create($request->only(['name', 'username', 'email', 'password']));
        $userActivation = UserActivationRepository::create($user->toArray());

        $data = $user->toArray();
        $data['token'] = $userActivation->token;

        Mail::send('email.user_activation', $data, function ($message) use ($data) {
            $message->to($data['email']);
            $message->subject('Konfirmasi registrasi akun');
        });

        return $this->resJsonSuccess('Registrasi berhasil. Silakan cek email anda untuk aktivasi akun.', 201);
    }
}
