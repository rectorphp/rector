<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Empty_;
use RectorPrefix20220606\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Empty_>
 */
final class EmptyNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Empty_::class;
    }
    /**
     * @param Empty_ $node
     */
    public function resolve(Node $node) : ?string
    {
        return 'empty';
    }
}
