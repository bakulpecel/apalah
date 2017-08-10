<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            $paginator = User::where('active', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($request->header('paginator'));

            $users = $paginator->getCollection();

            $response = fractal()
                ->collection($users, new UserTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        $users = User::where('active', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        $response = fractal()
            ->collection($users)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

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

    public function profile()
    {
        $user = User::find(Auth::user()->id);

        $response = fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:20',
            'phone_number' => 'numeric',
            'photo'    => 'image|mimes:jpeg,jpg,png|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        if ($request->hasFile('photo')) {
            Storage::disk('local')
                ->put('avatar/' . $imageName = time() . '.' . $request->photo->getClientOriginalExtension(),
                    File::get($request->file('photo'))
                );
        }

        $user = User::find(Auth::user()->id)->update([
            'name'     => $request->name,
            'phone_number' => $request->phone_number,
            'photo'    => $imageName ?? Auth::user()->photo,
        ]);

        return $this->resJsonSuccess('Akun berhasil diperbarui.', 200);

    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password'         => 'required|min:6:max:32',
            'new_password'         => 'required|min:6:max:32',
            'confirm_new_password' => 'required|min:6:max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        if ($request->new_password != $request->confirm_new_password) {
            return $this->resJsonError('Password baru dan Password konfirmasi tidak sama!.', 400);
        }

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return $this->resJsonError('Password lama anda salah!.', 400);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return $this->resJsonSuccess('Password berhasil diupdate.', 200);
    }

    public function destroy($username)
    {   
        $username = substr($username, 1);
        
        $user = User::where('username', $username)
            ->first();

        if (!$user) {
            return $this->resJsonError('Tidak menemukan pengguna yang akan dihapus!.', 404);
        }

        $user->delete();

        return $this->resJsonSuccess('Berhasil menghapus akun.', 200);
    }
}
