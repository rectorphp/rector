<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeReplacer;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyFetchWithVariableReplacer
{
    public function __construct(
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param array<string, string[]> $methodsByPropertyName
     */
    public function replacePropertyFetchesByVariable(Class_ $class, array $methodsByPropertyName): void
    {
        foreach ($methodsByPropertyName as $propertyName => $methodNames) {
            $methodName = $methodNames[0];
            $classMethod = $class->getMethod($methodName);
            if (! $classMethod instanceof ClassMethod) {
                continue;
            }

            $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
                (array) $classMethod->getStmts(),
                function (Node $node) use ($propertyName): ?Variable {
                    if (! $node instanceof PropertyFetch) {
                        return null;
                    }

                    if (! $this->nodeNameResolver->isName($node, $propertyName)) {
                        return null;
                    }

                    return new Variable($propertyName);
                }
            );
        }
    }
}
