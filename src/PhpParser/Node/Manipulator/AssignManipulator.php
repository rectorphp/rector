<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class AssignManipulator
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    public function __construct(NameResolver $nameResolver, PropertyFetchManipulator $propertyFetchManipulator)
    {
        $this->nameResolver = $nameResolver;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
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

        return (bool) $this->propertyFetchManipulator->matchPropertyFetch($node->var);
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
        $propertyFetch = $this->propertyFetchManipulator->matchPropertyFetch($node->var);
        if ($propertyFetch === null) {
            return false;
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
