<?php

declare(strict_types=1);

namespace Rector\Transform\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class FullyQualifiedNameResolver
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Node[] $nodes
     */
    public function resolveFullyQualifiedName(array $nodes, string $shortClassName): string
    {
        $foundNode = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (! $foundNode instanceof Namespace_) {
            return $shortClassName;
        }

        $namespaceName = $this->nodeNameResolver->getName($foundNode);
        if ($namespaceName === null) {
            return $shortClassName;
        }

        return $namespaceName . '\\' . $shortClassName;
    }
}
