<?php

namespace Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Fixture;

use Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Source\LocalEventDispatcher;

/**
 * This docblock is being deleted when there's an empty docblock after it.
 */

/**
 *
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

final class CommentsForTypedProperty
{
    private $eventDispatcher;

    public function __construct(LocalEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Fixture;

use Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Source\LocalEventDispatcher;

/**
 * This docblock is being deleted when there's an empty docblock after it.
 */

/**
 *
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

final class CommentsForTypedProperty
{
    private \Rector\Core\Tests\PhpParser\Printer\CommentPreserving\Source\LocalEventDispatcher $eventDispatcher;

    public function __construct(LocalEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}

?>
