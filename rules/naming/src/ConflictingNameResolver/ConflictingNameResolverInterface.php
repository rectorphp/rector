<?php

declare(strict_types=1);

namespace Rector\Naming\ConflictingNameResolver;

use PhpParser\Node;

interface ConflictingNameResolverInterface
{
    public function resolve(Node $node): array;
}
