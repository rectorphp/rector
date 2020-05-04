<?php

namespace Rector\Core\Tests\Issues\Issue3222\Fixture;

/**
 * This docblock is being deleted when there's an empty docblock after it.
 */

/**
 *
 */

final class Fixture3222
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue3222\Fixture;

/**
 * This docblock is being deleted when there's an empty docblock after it.
 */

/**
 *
 */

final class Fixture3222
{
    private \Rector\Core\Tests\Issues\Issue3222\Fixture\EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
