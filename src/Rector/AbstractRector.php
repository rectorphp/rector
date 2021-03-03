<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use Rector\Core\Contract\Rector\PhpRectorInterface;

/**
 * @see \Rector\Testing\PHPUnit\AbstractRectorTestCase
 */
abstract class AbstractRector extends AbstractTemporaryRector implements PhpRectorInterface
{
}
