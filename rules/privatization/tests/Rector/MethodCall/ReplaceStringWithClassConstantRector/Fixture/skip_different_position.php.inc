<?php

namespace Rector\Privatization\Tests\Rector\MethodCall\ReplaceStringWithClassConstantRector\Fixture;

class SkipDifferentPosition
{
    public function run()
    {
        $this->call(1000, 'name');
    }
}
