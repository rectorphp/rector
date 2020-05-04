<?php

namespace Rector\Core\Tests\Issues\Issue3222\Fixture;

/**
 * The empty line after this docblock is being deleted.
 */

/**
 * Another dockblock.
 *
 *
 * first_function()
 *
 *
 * second_function()
 * third_function()
 * fourth_function()
 */

final class Fixture3222WithManyComments
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
 * The empty line after this docblock is being deleted.
 */

/**
 * Another dockblock.
 *
 *
 * first_function()
 *
 *
 * second_function()
 * third_function()
 * fourth_function()
 */

final class Fixture3222WithManyComments
{
    private \Rector\Core\Tests\Issues\Issue3222\Fixture\EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
