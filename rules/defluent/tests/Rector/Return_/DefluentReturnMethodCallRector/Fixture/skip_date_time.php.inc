<?php

namespace Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use DateTime;

class SkipDateTime
{
    public function someFunction()
    {
        $nextMonth = (new DateTime())->modify('+1 month')->format('Y-m-d');
    }
}
