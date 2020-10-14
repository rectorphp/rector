<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_ as ClassStmt;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class ClassBuilder extends Class_
{
}
