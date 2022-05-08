<?php

declare(strict_types=1);

namespace Rector\Naming\PropertyRenamer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyFetchRenamer
{
    public function __construct(
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly PropertyFetchAnalyzer $propertyFetchAnalyzer
    ) {
    }

    public function renamePropertyFetchesInClass(ClassLike $classLike, string $currentName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $classLike,
            function (Node $node) use ($currentName, $expectedName): ?Node {
                if (! $this->propertyFetchAnalyzer->isLocalPropertyFetchName($node, $currentName)) {
                    return null;
                }

                /** @var StaticPropertyFetch|PropertyFetch $node */
                $node->name = $node instanceof PropertyFetch
                    ? new Identifier($expectedName)
                    : new VarLikeIdentifier($expectedName);

                return $node;
            }
        );
    }
}
