<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeStaticClosureRector\Fixture;

final class SkipNonStatic
{
    public function run()
    {
        return function () {
            return true;
        };
    }
}
