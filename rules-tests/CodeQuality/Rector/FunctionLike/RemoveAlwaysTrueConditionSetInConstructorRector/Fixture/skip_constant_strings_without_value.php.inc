<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipConstantStringsWithoutValue
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
