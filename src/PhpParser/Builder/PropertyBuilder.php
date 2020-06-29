<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Property;
use PhpParser\Node\Stmt\Property as PropertyNode;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 *
 * @method PropertyNode getNode()
 */
final class PropertyBuilder extends Property
{
}
