<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumPrice;
use App\Models\Transaction;
use App\Transformers\PriceTransformer;
use App\Transformers\TransactionTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class SubcriptionController extends Controller
{
    private $baseUrlApp = 'https://app.sandbox.midtrans.com/snap/v1/';
    private $baseUrlApi = 'https://api.sandbox.midtrans.com/v2/';
    private $serverKey = 'VT-server-XKzWkGRPWv9VnB6pUwTqYn9u';

    private function clientApp()
    {
        return new Client([
            'base_uri' => $this->baseUrlApp,
            'headers'  => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey)
            ],
        ]);
    }

    private function clientApi()
    {
        return new Client([
            'base_uri' => $this->baseUrlApi,
            'headers'  => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey)
            ],
        ]);
    }

    public function getToken($month)
    {
        $premiumPrice = PremiumPrice::where('month', $month)
            ->first();

        if (!$premiumPrice) {
            return $this->resJsonError('Belum tersedia!.', 400);
        }

        try {
            $response = $this->clientApp()->request('POST', 'transactions', [
                'json' => [
                    'transaction_details' => [
                        'order_id'     => $orderId = 'Payment-' . str_random(8) . time(),
                        'gross_amount' => $orderTotal = $premiumPrice->price,
                    ],
                    'credit_card' => [
                        'secure' => true,
                    ],
                    'customer_details' => [
                        'first_name' => Auth::user()->username,
                        'email'      => Auth::user()->email,
                        'phone'      => Auth::user()->phone_number,
                    ],
                ],
            ])->getBody();

            $response = json_decode($response);

            $transactions = Transaction::create([
                'user_id'     => Auth::user()->id,
                'order_id'    => $orderId,
                'order_total' => $orderTotal,
                'token'       => $response->token,
                'status'      => 'Pending',
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse()->getBody();
        }

        return response()
            ->json($response, 200);
    }

    public function price()
    {
        $premiumPrice = PremiumPrice::all();

        $response = fractal()
            ->collection($premiumPrice)
            ->transformWith(new PriceTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

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

    public function transactions(Request $request)
    {
        if ($request->hasHeader('paginator')) {
            if (Auth::user()->role_id === 1) {
                $paginator = Transaction::orderBy('created_at', 'desc')
                    ->paginate($request->header('paginator'));
            } else {
                $paginator = Transaction::where('user_id', Auth::user()->id)
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->header('paginator'));
            }

            $transactions = $paginator->getCollection();

            $response = fractal()
                ->collection($transactions)
                ->transformWith(new TransactionTransformer)
                ->paginateWith(new IlluminatePaginatorAdapter($paginator))
                ->toArray();

            return response()
                ->json($response, 200);
        }

        if (Auth::user()->role_id === 1) {
            $transactions = Transaction::orderBy('created_at', 'desc')
                ->get();
        } else {
            $transactions = Transaction::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $response = fractal()
            ->collection($transactions)
            ->transformWith(new TransactionTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }

    public function transactionsDetail(Transaction $orderId)
    {
        try {
            $data = $this->clientApi()->request('GET', $orderId->order_id . '/status');

            $content = json_decode($data->getBody());
            $orderId->status = $content->transaction_status ?? $orderId->status;
            $orderId->update();
        } catch (GuzzleException $e) {
            // 
        }

        $response = fractal()
            ->item($orderId)
            ->transformWith(new TransactionTransformer)
            ->toArray();

        return response()
            ->json($response, 200);
    }
}
