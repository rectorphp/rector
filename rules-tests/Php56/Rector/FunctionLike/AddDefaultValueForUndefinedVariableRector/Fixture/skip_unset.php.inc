<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipUnset
{
    public function run()
    {
        $checks = null;
        $events = [];

        foreach ($events as $event) {
            $checks[] = true;

            unset($checks);
        }
    }
}
