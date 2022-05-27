<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Empty_>
 */
final class EmptyNameResolver implements \Rector\NodeNameResolver\Contract\NodeNameResolverInterface
{
    public function getNode() : string
    {
        return \PhpParser\Node\Expr\Empty_::class;
    }
    /**
     * @param Empty_ $node
     */
    public function resolve(\PhpParser\Node $node) : ?string
    {
        return 'empty';
    }
}
