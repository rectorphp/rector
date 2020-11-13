<?php

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, true);

        $carbon->diffForHumans(null, false);
    }
}

?>
-----
<?php

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
    }
}

?>
