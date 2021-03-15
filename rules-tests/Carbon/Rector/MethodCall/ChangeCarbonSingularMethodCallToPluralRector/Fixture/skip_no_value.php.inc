<?php

namespace Rector\Tests\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

use Carbon\Carbon;

final class SkipNoValue
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute();
    }
}
