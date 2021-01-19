<?php

declare(strict_types=1);

namespace Rector\Legacy\Naming;

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
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if (! $namespace instanceof Namespace_) {
            return $shortClassName;
        }

        $namespaceName = $this->nodeNameResolver->getName($namespace);
        if ($namespaceName === null) {
            return $shortClassName;
        }

        return $namespaceName . '\\' . $shortClassName;
    }
}
