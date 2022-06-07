<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
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
