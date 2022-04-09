<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

trait SkipInTrait
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
