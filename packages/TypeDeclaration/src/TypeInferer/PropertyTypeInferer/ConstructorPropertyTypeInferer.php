<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

final class ConstructorPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @return string[]|IdentifierValueObject[]
     */
    public function inferProperty(Property $property): array
    {
        /** @var Class_ $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);

        $classMethod = $class->getMethod('__construct');
        if ($classMethod === null) {
            return [];
        }

        $propertyName = $this->nameResolver->getName($property);

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
                $types = $this->resolveMoreSpecificArrayType($classMethod, $propertyName);
            } else {
                $types[] = $type;
            }

            if ($this->isParamNullable($param)) {
                $types[] = 'null';
            }

            return array_unique($types);
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

            $paramStaticType = $this->nodeTypeResolver->getStaticType($node);

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
    private function resolveParamForPropertyFetch(ClassMethod $classMethod, string $propertyName): ?Param
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

    private function removePreSlash(string $content): string
    {
        return ltrim($content, '\\');
    }

    private function isParamNullable(Param $param): bool
    {
        if ($param->type instanceof NullableType) {
            return true;
        }

        if ($param->default) {
            $defaultValueStaticType = $this->nodeTypeResolver->getStaticType($param->default);
            if ($defaultValueStaticType instanceof NullType) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return IdentifierValueObject|string|null
     */
    private function resolveParamTypeToString(Param $param)
    {
        if ($param->type === null) {
            return null;
        }

        if ($param->type instanceof NullableType) {
            return $this->nameResolver->getName($param->type->type);
        }

        // special case for alias
        if ($param->type instanceof FullyQualified) {
            $fullyQualifiedName = $param->type->toString();
            $originalName = $param->type->getAttribute('originalName');

            if ($fullyQualifiedName && $originalName instanceof Name) {
                // if the FQN has different ending than the original, it was aliased and we need to return the alias
                if (! Strings::endsWith($fullyQualifiedName, '\\' . $originalName->toString())) {
                    return new IdentifierValueObject($originalName->toString(), true);
                }
            }
        }

        return $this->nameResolver->getName($param->type);
    }

    /**
     * @return string[]
     */
    private function resolveMoreSpecificArrayType(ClassMethod $classMethod, string $propertyName): array
    {
        $paramStaticTypeAsString = $this->getResolveParamStaticTypeAsString($classMethod, $propertyName);
        if ($paramStaticTypeAsString) {
            return explode('|', $paramStaticTypeAsString);
        }

        return ['array'];
    }
}
