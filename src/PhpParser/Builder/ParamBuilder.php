<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\Node\Param as ParamNode;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class ParamBuilder extends Param
{
}
