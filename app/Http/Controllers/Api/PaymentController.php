<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PremiumPrice;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    private $baseUrl = 'https://app.sandbox.midtrans.com/snap/v1/';
    private $serverKey = 'VT-server-XKzWkGRPWv9VnB6pUwTqYn9u';

    private function client()
    {
        return new Client([
            'base_uri' => $this->baseUrl,
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
            $response = $this->client()->request('POST', 'transactions', [
                'json' => [
                    'transaction_details' => [
                        'order_id'     => $orderId = 'Payment-' . str_random(8) . time(),
                        'gross_amount' => $orderTotal = $premiumPrice->price,
                    ],
                    'credit_card' => [
                        'secure' => true,
                    ],
                    'item_details' => [
                        'id'       => 'Paket-' . $premiumPrice->month . ' Bulan',
                        'price'    => PremiumPrice::find(1)->price,
                        'quantity' => $premiumPrice->month,
                        'name'     => 'Premium member',
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
                'status'      => 'On Process'
            ]);
        } catch (GuzzleException $e) {
            $response = $e->getResponse()->getBody();
        }

        return response()
            ->json($response, 200);
    }
}
