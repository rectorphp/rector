<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class IterablePropertyType
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
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class IterablePropertyType
{
    /**
     * @var HandlerInterface[]
     */
    private iterable $handlers;

    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }
}

?>
