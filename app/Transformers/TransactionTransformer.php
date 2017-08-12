<?php

namespace App\Transformers;

use App\Models\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Transaction $transaction)
    {
        return [
            'username'    => $transaction->user->username,
            'email'       => $transaction->user->email,
            'order_id'    => $transaction->order_id,
            'order_total' => $transaction->order_total,
            'status'      => $transaction->status,
        ];
    }
}
