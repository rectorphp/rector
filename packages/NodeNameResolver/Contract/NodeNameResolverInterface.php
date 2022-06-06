<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNameResolver\Contract;

use RectorPrefix20220606\PhpParser\Node;
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
