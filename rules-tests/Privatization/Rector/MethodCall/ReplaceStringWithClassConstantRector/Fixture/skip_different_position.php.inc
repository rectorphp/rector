<?php

namespace Rector\Tests\Privatization\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

class SkipDifferentPosition
{
    public function run()
    {
        $this->call(1000, 'name');
    }
}
