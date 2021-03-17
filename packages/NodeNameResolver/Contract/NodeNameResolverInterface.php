<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\Contract;

use PhpParser\Node;

interface NodeNameResolverInterface
{
    public function getNode(): string;

    public function resolve(Node $node): ?string;
}
