<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubcriptionController extends Controller
{
    public function setPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month1' => 'required|integer',
            'month3' => 'required|integer',
            'month6' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 400,
                    'message' => $validator->errors(),
                ],
            ], 400);
        }

        PremiumPrice::where('month', '=', 1)->update(['price' => $request->month1]);
        PremiumPrice::where('month', '=', 3)->update(['price' => $request->month3]);
        PremiumPrice::where('month', '=', 6)->update(['price' => $request->month6]);

        return $this->resJsonSuccess('Harga berlangganan telah diupdate.', 200);
    }
}
