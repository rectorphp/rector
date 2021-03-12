<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

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
        if ($this->value) {
            $maybe = 'yes';
            return 'she says ' . $maybe;
        }
    }

    public function another()
    {
        $this->value = null;
    }
}
