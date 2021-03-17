<?php

namespace Rector\Tests\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinutes($value);
    }
}

?>
