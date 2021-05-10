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
    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function renamePropertyFetchesInClass(ClassLike $classLike, string $currentName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $classLike,
            function (Node $node) use ($currentName, $expectedName): ?Node {
                if ($node instanceof PropertyFetch && $this->nodeNameResolver->isLocalPropertyFetchNamed(
                    $node,
                    $currentName
                )) {
                    $node->name = new Identifier($expectedName);
                    return $node;
                }

                if (! $node instanceof StaticPropertyFetch) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node->name, $currentName)) {
                    return null;
                }

                $node->name = new VarLikeIdentifier($expectedName);
                return $node;
            }
        );
    }
}
