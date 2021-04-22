<?php

interface HandlerInterface {}

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class SkipIterablePropertyType
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }
}
?>
