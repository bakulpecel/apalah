<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function resJsonSuccess($message, $code)
    {
        return response()->json([
            'success' => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $code);
    }

    public function resJsonError($message, $code)
    {
        return response()->json([
            'error' => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $code);
    }
}
