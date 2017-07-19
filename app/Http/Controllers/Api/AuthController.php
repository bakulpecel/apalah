<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivation;
use App\Transformers\UserTransformer;

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

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => 4,
            'active'   => 0,
        ]);

        $userActivation = UserActivation::create([
            'email'      => $user->email,
            'token'      => str_random(60),
            'expired_at' => Carbon::now('Asia/Jakarta')->addDay(1),
            'created_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $data = $user->toArray();
        $data['token'] = $userActivation->token;

        Mail::send('email.user_activation', $data, function ($message) use ($data) {
            $message->to($data['email']);
            $message->subject('Konfirmasi registrasi akun');
        });

        return $this->resJsonSuccess('Registrasi berhasil. Silakan cek email anda untuk aktivasi akun.', 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:5|max:15|alpha_num',
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

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return $this->resJsonError('Username atau Password salah!.', 401);
        }

        if (Auth::user()->active === 0) {
            return $this->resJsonError('Login gagal!. Silakan aktivasi akun anda terlebih dahulu.', 401);
        }

        User::find(Auth::user()->id)
            ->update(['api_token' => bcrypt(time().str_random(60))]);

        $response = fractal()
            ->item($user = User::find(Auth::user()->id))
            ->transformWith(new UserTransformer)
            ->addMeta(['api_token' => $user->api_token])
            ->toArray();

        return response()
            ->json($response, 200);
    }
}
