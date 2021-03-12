<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Matcher;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyAssignMatcher
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * Covers:
     * - $this->propertyName = $expr;
     * - $this->propertyName[] = $expr;
     */
    public function matchPropertyAssignExpr(Assign $assign, string $propertyName): ?Expr
    {
        if ($this->isPropertyFetch($assign->var)) {
            if (! $this->nodeNameResolver->isName($assign->var, $propertyName)) {
                return null;
            }

            return $assign->expr;
        }

        if ($assign->var instanceof ArrayDimFetch && $this->isPropertyFetch($assign->var->var)) {
            if (! $this->nodeNameResolver->isName($assign->var->var, $propertyName)) {
                return null;
            }

            return $assign->expr;
        }

        return null;
    }

    private function isPropertyFetch(Node $node): bool
    {
        if ($node instanceof PropertyFetch) {
            return true;
        }

        return $node instanceof StaticPropertyFetch;
    }
}
