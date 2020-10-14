<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class MethodBuilder extends Method
{
}
