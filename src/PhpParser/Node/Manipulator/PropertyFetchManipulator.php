<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Broker\Broker;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

/**
 * Read-only utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchManipulator
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        Broker $broker,
        NameResolver $nameResolver,
        CallableNodeTraverser $callableNodeTraverser
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->broker = $broker;
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @required
     */
    public function autowirePropertyFetchManipulator(AssignManipulator $assignManipulator): void
    {
        $this->assignManipulator = $assignManipulator;
    }

    public function isPropertyToSelf(PropertyFetch $propertyFetch): bool
    {
        if (! $this->nameResolver->isName($propertyFetch->var, 'this')) {
            return false;
        }

        /** @var Class_|null $class */
        $class = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return false;
        }

        if (! $this->nameResolver->isName($propertyFetch->var, 'this')) {
            return false;
        }

        foreach ($class->getProperties() as $property) {
            if ($this->nameResolver->areNamesEqual($property->props[0], $propertyFetch)) {
                return true;
            }
        }

        return false;
    }

    public function isMagicOnType(Node $node, Type $type): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        $varNodeType = $this->nodeTypeResolver->resolve($node);

        if ($varNodeType instanceof ErrorType) {
            return true;
        }

        if ($varNodeType instanceof MixedType) {
            return false;
        }

        if ($varNodeType->isSuperTypeOf($type)->yes()) {
            return false;
        }

        $nodeName = $this->nameResolver->getName($node);
        if ($nodeName === null) {
            return false;
        }

        return ! $this->hasPublicProperty($node, $nodeName);
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
            $propertyName = $this->nameResolver->getName($node->expr);
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
    public function isVariableAssignToThisPropertyFetch(Node $node, string $variableName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->expr instanceof Variable) {
            return false;
        }

        if (! $this->nameResolver->isName($node->expr, $variableName)) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }
        // must be local property
        return $this->nameResolver->isName($node->var->var, 'this');
    }

    /**
     * @param string[] $propertyNames
     */
    public function isLocalPropertyOfNames(Expr $expr, array $propertyNames): bool
    {
        if (! $this->isLocalProperty($expr)) {
            return false;
        }

        /** @var PropertyFetch $expr */
        return $this->nameResolver->isNames($expr->name, $propertyNames);
    }

    public function isLocalProperty(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        return $this->nameResolver->isName($node->var, 'this');
    }

    public function getFirstVariableAssignedToPropertyOfName(
        ClassMethod $classMethod,
        string $propertyName
    ): ?Variable {
        $variable = null;

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$variable
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->isLocalPropertyOfNames($node->var, [$propertyName])) {
                return null;
            }

            if (! $node->expr instanceof Variable) {
                return null;
            }

            $variable = $node->expr;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $variable;
    }

    /**
     * @return Expr[]
     */
    public function getExprsAssignedToPropertyName(ClassMethod $classMethod, string $propertyName): array
    {
        $assignedExprs = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            $propertyName,
            &$assignedExprs
        ) {
            if (! $this->assignManipulator->isLocalPropertyAssignWithPropertyNames($node, [$propertyName])) {
                return null;
            }

            /** @var Assign $node */
            $assignedExprs[] = $node->expr;
        });

        return $assignedExprs;
    }

    /**
     * In case the property name is different to param name:
     *
     * E.g.:
     * (SomeType $anotherValue)
     * $this->value = $anotherValue;
     * ↓
     * (SomeType $anotherValue)
     */
    public function resolveParamForPropertyFetch(ClassMethod $classMethod, string $propertyName): ?Param
    {
        $assignedParamName = null;

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$assignedParamName
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->nameResolver->isName($node->var, $propertyName)) {
                return null;
            }

            $assignedParamName = $this->nameResolver->getName($node->expr);

            return NodeTraverser::STOP_TRAVERSAL;
        });

        /** @var string|null $assignedParamName */
        if ($assignedParamName === null) {
            return null;
        }

        /** @var Param $param */
        foreach ((array) $classMethod->params as $param) {
            if (! $this->nameResolver->isName($param, $assignedParamName)) {
                continue;
            }

            return $param;
        }

        return null;
    }

    /**
     * @param Node $node
     * @return PropertyFetch|StaticPropertyFetch|null
     */
    public function matchPropertyFetch(Node $node): ?Node
    {
        if ($node instanceof PropertyFetch) {
            return $node;
        }

        if ($node instanceof StaticPropertyFetch) {
            return $node;
        }

        if ($node instanceof ArrayDimFetch) {
            $nestedNode = $node->var;

            while ($nestedNode instanceof ArrayDimFetch) {
                $nestedNode = $nestedNode->var;
            }

            return $this->matchPropertyFetch($nestedNode);
        }

        return null;
    }

    private function hasPublicProperty(PropertyFetch $propertyFetch, string $propertyName): bool
    {
        $nodeScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
            throw new ShouldNotHappenException();
        }

        $propertyFetchType = $nodeScope->getType($propertyFetch->var);
        if ($propertyFetchType instanceof ObjectType) {
            $propertyFetchType = $propertyFetchType->getClassName();
        }

        if (! is_string($propertyFetchType)) {
            return false;
        }

        if (! $this->broker->hasClass($propertyFetchType)) {
            return false;
        }

        $classReflection = $this->broker->getClass($propertyFetchType);
        if (! $classReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $classReflection->getProperty($propertyName, $nodeScope);

        return $propertyReflection->isPublic();
    }
}
