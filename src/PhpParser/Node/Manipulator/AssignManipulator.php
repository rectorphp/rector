<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class AssignManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * Checks:
     * $this->x = y;
     * $this->x[] = y;
     */
    public function isLocalPropertyAssign(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if ($node->var instanceof ArrayDimFetch) {
            $potentialPropertyFetch = $node->var->var;
        } else {
            $potentialPropertyFetch = $node->var;
        }

        return $potentialPropertyFetch instanceof PropertyFetch || $potentialPropertyFetch instanceof StaticPropertyFetch;
    }

    /**
     * Is: "$this->value = <$value>"
     *
     * @param string[] $propertyNames
     */
    public function isLocalPropertyAssignWithPropertyNames(Node $node, array $propertyNames): bool
    {
        if (! $this->isLocalPropertyAssign($node)) {
            return false;
        }

        /** @var Assign $node */
        if ($node->var instanceof ArrayDimFetch) {
            /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
            $propertyFetch = $node->var->var;
        } else {
            /** @var PropertyFetch|StaticPropertyFetch $propertyFetch */
            $propertyFetch = $node->var;
        }

        return $this->nameResolver->isNames($propertyFetch, $propertyNames);
    }

    /**
     * Covers:
     * - $this->propertyName = <$expr>;
     * - self::$propertyName = <$expr>;
     * - $this->propertyName[] = <$expr>;
     * - self::$propertyName[] = <$expr>;
     */
    public function matchPropertyAssignExpr(Assign $assign, string $propertyName): ?Expr
    {
        if (! $this->isLocalPropertyAssignWithPropertyNames($assign, [$propertyName])) {
            return null;
        }

        return $assign->expr;
    }

    /**
     * Matches:
     * each() = [1, 2];
     */
    public function isListToEachAssign(Assign $assign): bool
    {
        if (! $assign->expr instanceof FuncCall) {
            return false;
        }

        if (! $assign->var instanceof List_) {
            return false;
        }

        return $this->nameResolver->isName($assign->expr, 'each');
    }
}
