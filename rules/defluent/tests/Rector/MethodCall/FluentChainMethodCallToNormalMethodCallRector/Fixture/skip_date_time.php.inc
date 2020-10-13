<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use DateTime;

class SkipDateTime
{
    public function run($values)
    {
        $dateTime = new DateTime();
        foreach ($values as $value) {
            $dateTime->modify(1)->format('Y-m-D');
        }
    }
}
