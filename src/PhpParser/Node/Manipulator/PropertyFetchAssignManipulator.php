<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyFetchAssignManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @return string[]
     */
    public function getPropertyNamesOfAssignOfVariable(Node $node, string $paramName): array
    {
        $propertyNames = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            $paramName,
            &$propertyNames
        ) {
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
    private function isVariableAssignToThisPropertyFetch(Node $node, string $variableName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->expr instanceof Variable) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->expr, $variableName)) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }
        // must be local property
        return $this->nodeNameResolver->isName($node->var->var, 'this');
    }
}
