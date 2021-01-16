<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\VarLikeIdentifier;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyFetchRenamer
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function renamePropertyFetchesInClass(ClassLike $classLike, string $currentName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $classLike,
            function (Node $node) use ($currentName, $expectedName): ?Node {
                if ($this->nodeNameResolver->isLocalPropertyFetchNamed(
                        $node,
                        $currentName
                    ) && $node instanceof PropertyFetch) {
                    $node->name = new Identifier($expectedName);
                    return $node;
                }

                if ($this->nodeNameResolver->isLocalStaticPropertyFetchNamed($node, $currentName)) {
                    if (! $node instanceof StaticPropertyFetch) {
                        return null;
                    }

                    $node->name = new VarLikeIdentifier($expectedName);
                    return $node;
                }

                return null;
            }
        );
    }
}
