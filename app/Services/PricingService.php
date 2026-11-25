<?php

namespace App\Services;

use App\Models\Customer;

/**
 * Port of legacy calculate_stiches_price() from ajax-get-DOrder-contents.php.
 * Rules (in order):
 *  1. stitches >= Max_Order_Value (when set)  -> price of Max stitches at Normal_Fee per 1000
 *  2. stitches <= Min_Order_Value (when set)  -> price of Min stitches at Normal_Fee per 1000
 *  3. first matching customer price range      -> per-1000 rate, or fixed price
 *  4. otherwise                                -> stitches at Normal_Fee per 1000
 */
class PricingService
{
    public function calculate(Customer $customer, int $stitches): float
    {
        $normalFee = (float) $customer->Normal_Fee;
        $max = (int) $customer->Max_Order_Value;
        $min = (int) $customer->Min_Order_Value;

        if ($max !== 0 && $stitches >= $max) {
            return round(($max * $normalFee) / 1000, 2);
        }

        if ($min !== 0 && $stitches <= $min) {
            return round(($min * $normalFee) / 1000, 2);
        }

        foreach ($customer->priceAttributes as $range) {
            if ((int) $range->No_of_Stiches_Start <= $stitches && $stitches <= (int) $range->No_of_Stiches_End) {
                $rate = (float) $range->Price_per_Thousand_Stiches;
                $price = (int) $range->Is_Fixed === 0 ? ($stitches * $rate) / 1000 : $rate;

                if ($price != 0.0) {
                    return round($price, 2);
                }
                break;
            }
        }

        return round(($stitches * $normalFee) / 1000, 2);
    }
}
