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
     * @param TNode $node
     */
    public function resolve(Node $node) : ?string;
}
