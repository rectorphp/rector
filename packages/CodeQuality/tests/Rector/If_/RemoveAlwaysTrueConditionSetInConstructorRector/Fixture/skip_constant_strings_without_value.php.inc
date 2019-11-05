<?php

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipConstantStringWithoutValue
{
    private $smallValue;

    public function __construct()
    {
        $this->smallValue = '';
    }

    public function go()
    {
        if ($this->smallValue) {
            return 'no';
        }
    }
}
