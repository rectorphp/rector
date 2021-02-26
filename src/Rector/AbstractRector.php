<?php

declare(strict_types=1);

namespace Rector\Core\Rector;

use Rector\Core\Contract\Rector\CorePhpRectorInterface;

/**
 * @see \Rector\Testing\PHPUnit\AbstractRectorTestCase
 */
abstract class AbstractRector extends AbstractTemporaryRector implements CorePhpRectorInterface
{
}
