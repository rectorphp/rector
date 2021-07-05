<?php

declare (strict_types=1);
namespace Rector\Testing\Contract;

use PhpParser\Node;
interface NodeTraversableInterface
{
    /**
     * @param Node[] $nodes
     */
    public function traverseNodes($nodes) : void;
}
