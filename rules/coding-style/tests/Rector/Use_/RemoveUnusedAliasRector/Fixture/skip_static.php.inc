<?php

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector\Fixture;

use Stripe\PaymentIntent as StripePaymentIntent;

class SkipStatic
{
    public function run()
    {
        return StripePaymentIntent::create([
            'receipt_email' => auth()->user()->email,
        ]);
    }
}
