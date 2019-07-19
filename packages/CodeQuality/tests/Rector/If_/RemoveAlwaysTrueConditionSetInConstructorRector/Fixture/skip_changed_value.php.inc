<?php

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class SkipChangedValue
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        $this->value = null;

        if ($this->value) {
            $maybe = 'yes';
            return 'she says ' . $maybe;
        }
    }
}
