<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\PriceAttribute;
use App\Services\PricingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    private function customer(float $fee, int $min = 0, int $max = 0, array $ranges = []): Customer
    {
        $c = new Customer(['Normal_Fee' => $fee, 'Min_Order_Value' => $min, 'Max_Order_Value' => $max]);
        $c->setRelation('priceAttributes', collect(array_map(fn ($r) => new PriceAttribute([
            'No_of_Stiches_Start' => $r[0], 'No_of_Stiches_End' => $r[1],
            'Price_per_Thousand_Stiches' => $r[2], 'Is_Fixed' => $r[3],
        ]), $ranges)));

        return $c;
    }

    public static function cases(): array
    {
        return [
            'normal fee only' => [1.0, 0, 0, [], 8500, 8.50],
            'below minimum charges minimum' => [1.0, 4000, 0, [], 2500, 4.00],
            'at minimum charges minimum' => [1.0, 4000, 0, [], 4000, 4.00],
            'above maximum is capped' => [1.0, 0, 20000, [], 35000, 20.00],
            'per-thousand range' => [1.0, 0, 0, [[5000, 10000, 0.8, 0]], 7000, 5.60],
            'fixed range' => [1.0, 0, 0, [[5000, 10000, 6, 1]], 7000, 6.00],
            'outside ranges uses normal fee' => [1.0, 0, 0, [[5000, 10000, 6, 1]], 12000, 12.00],
            'zero range price falls back to normal fee' => [1.0, 0, 0, [[5000, 10000, 0, 0]], 7000, 7.00],
            'min check wins over range' => [1.0, 4000, 0, [[0, 10000, 9, 1]], 3000, 4.00],
        ];
    }

    #[DataProvider('cases')]
    public function test_price_matches_legacy_rules(float $fee, int $min, int $max, array $ranges, int $stitches, float $expected): void
    {
        $price = (new PricingService)->calculate($this->customer($fee, $min, $max, $ranges), $stitches);

        $this->assertEqualsWithDelta($expected, $price, 0.001);
    }
}
