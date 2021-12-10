<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\Contract;

use PhpParser\Node;
/**
 * @template TNode as Node
 */
interface NodeNameResolverInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNode() : string;
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : ?string;
}
