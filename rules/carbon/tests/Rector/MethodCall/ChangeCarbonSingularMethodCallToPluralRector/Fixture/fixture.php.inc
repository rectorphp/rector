<?php

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

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

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinutes($value);
    }
}

?>
