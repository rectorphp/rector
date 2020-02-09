<?php

declare(strict_types=1);

namespace Rector\Core\Contract\NameResolver;

use PhpParser\Node;

interface NodeNameResolverInterface
{
    public function getNode(): string;

    public function resolve(Node $node): ?string;
}
