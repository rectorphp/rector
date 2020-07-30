<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

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
