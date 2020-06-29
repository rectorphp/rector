<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Method;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 *
 * @method ClassMethod getNode()
 */
final class MethodBuilder extends Method
{
}
