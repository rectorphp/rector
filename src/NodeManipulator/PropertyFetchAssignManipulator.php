<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class PropertyFetchAssignManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }

    /**
     * @return string[]
     */
    public function getPropertyNamesOfAssignOfVariable(Node $node, string $paramName): array
    {
        $propertyNames = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            $paramName,
            &$propertyNames
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->isVariableAssignToThisPropertyFetch($node, $paramName)) {
                return null;
            }

            /** @var Assign $node */
            $propertyName = $this->nodeNameResolver->getName($node->expr);
            if ($propertyName) {
                $propertyNames[] = $propertyName;
            }

            return null;
        });

        return $propertyNames;
    }

    /**
     * Matches:
     * "$this->someValue = $<variableName>;"
     */
    private function isVariableAssignToThisPropertyFetch(Assign $assign, string $variableName): bool
    {
        if (! $assign->expr instanceof Variable) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($assign->expr, $variableName)) {
            return false;
        }

        if (! $assign->var instanceof PropertyFetch) {
            return false;
        }

        $propertyFetch = $assign->var;

        // must be local property
        return $this->nodeNameResolver->isName($propertyFetch->var, 'this');
    }
}
