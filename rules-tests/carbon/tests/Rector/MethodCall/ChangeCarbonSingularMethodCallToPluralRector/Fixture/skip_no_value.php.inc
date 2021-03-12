<?php

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

use Carbon\Carbon;

final class SkipNoValue
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute();
    }
}
