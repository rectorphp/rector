<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;

final class EmptyNameResolver implements NodeNameResolverInterface
{
    public function getNode(): string
    {
        return Empty_::class;
    }

    /**
     * @param Empty_ $node
     */
    public function resolve(Node $node): ?string
    {
        return 'empty';
    }
}
