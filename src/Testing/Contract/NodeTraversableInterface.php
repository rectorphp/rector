<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Contract;

use PhpParser\Node;

interface NodeTraversableInterface
{
    /**
     * @param Node[] $nodes
     */
    public function traverseNodes(array $nodes): void;
}
