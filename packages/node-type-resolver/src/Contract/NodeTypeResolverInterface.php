<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract;

use PhpParser\Node;
use PHPStan\Type\Type;

interface NodeTypeResolverInterface
{
    /**
     * @return class-string[]
     */
    public function getNodeClasses(): array;

    public function resolve(Node $node): Type;
}
