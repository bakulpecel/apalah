<?php

namespace App\Transformers;

use App\Models\PremiumPrice;
use League\Fractal\TransformerAbstract;

class PriceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(PremiumPrice $premiumPrice)
    {
        return [
            'period' => $premiumPrice->month . ' Bulan',
            'price'  => $premiumPrice->price,
        ];
    }
}
