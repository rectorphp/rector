<?php

interface HandlerInterface {}

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

interface HandlerInterface {}

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
