<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector\Fixture;

class SkipNonPublic
{
    protected function run($arg)
    {
        return $arg;
    }
}
