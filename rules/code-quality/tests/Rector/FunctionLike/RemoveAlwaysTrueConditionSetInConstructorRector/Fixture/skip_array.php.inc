<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipArray
{
    private $value;

    public function __construct(array $value)
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
}
