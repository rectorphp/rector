<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Builder;

use PhpParser\Builder\Namespace_;

/**
 * Fixed duplicated naming in php-parser and prevents confusion
 */
final class NamespaceBuilder extends Namespace_
{
}
