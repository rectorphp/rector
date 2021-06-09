<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyFetchAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    public function isLocalPropertyFetch(Node $node): bool
    {
        if ($node instanceof PropertyFetch) {
            if ($node->var instanceof MethodCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, 'this');
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->nodeNameResolver->isName($node->class, 'self');
        }

        return false;
    }

    public function isLocalPropertyFetchName(Node $node, string $desiredPropertyName): bool
    {
        if (! $this->isLocalPropertyFetch($node)) {
            return false;
        }

        /** @var PropertyFetch|StaticPropertyFetch $node */
        return $this->nodeNameResolver->isName($node->name, $desiredPropertyName);
    }

    public function containsLocalPropertyFetchName(ClassMethod $classMethod, string $propertyName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($classMethod, function (Node $node) use ($propertyName): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->name, $propertyName);
        });
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    public function isPropertyToSelf(Expr $expr): bool
    {
        if ($expr instanceof PropertyFetch && ! $this->nodeNameResolver->isName($expr->var, 'this')) {
            return false;
        }

        if ($expr instanceof StaticPropertyFetch && ! $this->nodeNameResolver->isName($expr->class, 'self')) {
            return false;
        }

        $classLike = $expr->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        foreach ($classLike->getProperties() as $property) {
            if (! $this->nodeNameResolver->areNamesEqual($property->props[0], $expr)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Matches:
     * "$this->someValue = $<variableName>;"
     */
    public function isVariableAssignToThisPropertyFetch(Node $node, string $variableName): bool
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

        return $this->isLocalPropertyFetch($node->var);
    }

    public function isFilledByConstructParam(Property $property): bool
    {
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return false;
        }

        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        $params = $classMethod->params;
        if ($params === []) {
            return false;
        }

        $stmts = (array) $classMethod->stmts;
        if ($stmts === []) {
            return false;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property->props[0]->name);
        $kindPropertyFetch = $property->isStatic()
            ? StaticPropertyFetch::class
            : PropertyFetch::class;

        return $this->isParamFilledStmts($params, $stmts, $propertyName, $kindPropertyFetch);
    }

    /**
     * @param string[] $propertyNames
     */
    public function isLocalPropertyOfNames(Node $node, array $propertyNames): bool
    {
        if (! $this->isLocalPropertyFetch($node)) {
            return false;
        }

        /** @var PropertyFetch $node */
        return $this->nodeNameResolver->isNames($node->name, $propertyNames);
    }

    /**
     * @param Param[] $params
     * @param Stmt[] $stmts
     */
    private function isParamFilledStmts(
        array $params,
        array $stmts,
        string $propertyName,
        string $kindPropertyFetch
    ): bool
    {
        foreach ($params as $param) {
            $paramVariable = $param->var;
            $isAssignWithParamVarName = $this->betterNodeFinder->findFirst($stmts, function (Node $node) use (
                $propertyName,
                $paramVariable,
                $kindPropertyFetch
            ): bool {
                if (! $node instanceof Assign) {
                    return false;
                }

                return $kindPropertyFetch === get_class($node->var) && $this->nodeNameResolver->isName(
                    $node->var,
                    $propertyName
                ) && $this->nodeComparator->areNodesEqual($node->expr, $paramVariable);
            });

            if ($isAssignWithParamVarName !== null) {
                return true;
            }
        }

        return false;
    }
}
