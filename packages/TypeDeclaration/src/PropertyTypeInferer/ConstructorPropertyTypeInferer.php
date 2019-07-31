<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

final class ConstructorPropertyTypeInferer extends AbstractPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferProperty(Node\Stmt\Property $property): array
    {
        /** @var Node\Stmt\Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $classMethod = $class->getMethod('__construct');
        if ($classMethod === null) {
            return [];
        }

        $propertyName = $this->nameResolver->resolve($property);

        $param = $this->resolveParamForPropertyFetch($classMethod, $propertyName);
        if ($param === null) {
            return [];
        }

        // A. infer from type declaration of parameter
        if ($param->type) {
            $type = $this->resolveParamTypeToString($param);
            if ($type === null) {
                return [];
            }

            $types = [];

            // it's an array - annotation → make type more precise, if possible
            if ($type === 'array') {
                $paramStaticTypeAsString = $this->getResolveParamStaticTypeAsString($classMethod, $propertyName);
                $types[] = $paramStaticTypeAsString ?? 'array';
            } else {
                $types[] = $type;
            }

            if ($this->isParamNullable($param)) {
                $types[] = 'null';
            }

            return $types;
        }

        return [];
    }

    public function getPriority(): int
    {
        return 800;
    }

    private function getResolveParamStaticTypeAsString(ClassMethod $classMethod, string $propertyName): ?string
    {
        $paramStaticType = $this->resolveParamStaticType($classMethod, $propertyName);
        if ($paramStaticType === null) {
            return null;
        }

        $typesAsStrings = $this->staticTypeToStringResolver->resolveObjectType($paramStaticType);

        foreach ($typesAsStrings as $i => $typesAsString) {
            $typesAsStrings[$i] = $this->removePreSlash($typesAsString);
        }

        return implode('|', $typesAsStrings);
    }

    private function resolveParamStaticType(ClassMethod $classMethod, string $propertyName): ?Type
    {
        $paramStaticType = null;

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$paramStaticType
        ): ?int {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->nameResolver->isName($node, $propertyName)) {
                return null;
            }

            $paramStaticType = $this->nodeTypeResolver->getNodeStaticType($node);

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $paramStaticType;
    }

    /**
     * In case the property name is different to param name:
     *
     * E.g.:
     * (SomeType $anotherValue)
     * $this->value = $anotherValue;
     * ↓
     * $anotherValue param
     */
    private function resolveParamForPropertyFetch(ClassMethod $classMethod, string $propertyName): ?Node\Param
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

            $assignedParamName = $this->nameResolver->resolve($node->expr);

            return NodeTraverser::STOP_TRAVERSAL;
        });

        /** @var string|null $assignedParamName */
        if ($assignedParamName === null) {
            return null;
        }

        /** @var Node\Param $param */
        foreach ((array) $classMethod->params as $param) {
            if (! $this->nameResolver->isName($param, $assignedParamName)) {
                continue;
            }

            return $param;
        }

        return null;
    }

    private function removePreSlash(string $content): string
    {
        return ltrim($content, '\\');
    }

    private function isParamNullable(Node\Param $param): bool
    {
        if ($param->type instanceof NullableType) {
            return true;
        }

        if ($param->default) {
            $defaultValueStaticType = $this->nodeTypeResolver->getNodeStaticType($param->default);
            if ($defaultValueStaticType instanceof NullType) {
                return true;
            }
        }

        return false;
    }

    private function resolveParamTypeToString(Node\Param $param): ?string
    {
        if ($param->type === null) {
            return null;
        }

        if ($param->type instanceof NullableType) {
            return $this->nameResolver->resolve($param->type->type);
        }

        return $this->nameResolver->resolve($param->type);
    }
}
