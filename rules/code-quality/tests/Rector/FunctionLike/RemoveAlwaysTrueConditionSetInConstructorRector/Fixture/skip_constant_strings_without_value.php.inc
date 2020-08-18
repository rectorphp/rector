<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

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
