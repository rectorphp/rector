<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\TraitUse;
use PhpParser\Node\Stmt\TraitUse as TraitUseStmt;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 *
 * @method TraitUseStmt getNode()
 */
final class TraitUseBuilder extends TraitUse
{
}
