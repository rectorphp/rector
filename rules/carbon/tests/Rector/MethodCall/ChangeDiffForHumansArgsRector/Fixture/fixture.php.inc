<?php

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector\Fixture;

use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans->diffForHumans(null, true);

        $carbon->diffForHumans->diffForHumans(null, false);
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
        $carbon->diffForHumans->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

        $carbon->diffForHumans->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
    }
}

?>
