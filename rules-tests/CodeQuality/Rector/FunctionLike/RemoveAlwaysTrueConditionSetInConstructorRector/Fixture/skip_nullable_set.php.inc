<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipNullableSet
{
    /**
     * @var mixed[]|null
     */
    private $callback;

    /**
     * @param mixed[]|null $callback
     */
    public function __construct(?array $callback)
    {
        $this->callback = $callback;
    }

    public function __toString()
    {
        $contentItems = [];

        if ($this->callback) {
            $contentItems['callback'] = 5;
        }

        return '...';
    }
}
