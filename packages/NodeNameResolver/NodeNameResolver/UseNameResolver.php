<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;

final class UseNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireUseNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return Use_::class;
    }

    /**
     * @param Use_ $node
     */
    public function resolve(Node $node): ?string
    {
        if ($node->uses === []) {
            return null;
        }

        $onlyUse = $node->uses[0];

        return $this->nodeNameResolver->getName($onlyUse);
    }
}
