<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipPublic
{
    /**
     * This value can be changed anytime to anything
     */
    public $value;

    public function __construct()
    {
        $this->value = 10000;
    }

    public function go()
    {
        if ($this->value) {
            return true;
        }

        return false;
    }
}
