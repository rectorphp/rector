<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionClass;

final class ParsedClassConstFetchNodeCollector
{
    /**
     * @var string[][][]
     */
    private $classConstantFetchByClassAndName = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * To prevent circular reference
     * @required
     */
    public function autowireParsedClassConstFetchNodeCollector(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function collect(Node $node): void
    {
        if (! $node instanceof ClassConstFetch) {
            return;
        }

        $constantName = $this->nodeNameResolver->getName($node->name);
        if ($constantName === 'class') {
            // this is not a manual constant
            return;
        }
        if ($constantName === null) {
            // this is not a manual constant
            return;
        }

        $resolvedClassType = $this->nodeTypeResolver->resolve($node->class);

        $className = $this->resolveClassTypeThatContainsConstantOrFirstUnioned($resolvedClassType, $constantName);
        if ($className === null) {
            return;
        }

        // current class
        $classOfUse = $node->getAttribute(AttributeKey::CLASS_NAME);

        $this->classConstantFetchByClassAndName[$className][$constantName][] = $classOfUse;

        $this->classConstantFetchByClassAndName[$className][$constantName] = array_unique(
            $this->classConstantFetchByClassAndName[$className][$constantName]
        );
    }

    /**
     * @return string[][][]
     */
    public function getClassConstantFetchByClassAndName(): array
    {
        return $this->classConstantFetchByClassAndName;
    }

    private function resolveClassTypeThatContainsConstantOrFirstUnioned(
        Type $resolvedClassType,
        string $constantName
    ): ?string {
        $className = $this->matchClassTypeThatContainsConstant($resolvedClassType, $constantName);
        if ($className !== null) {
            return $className;
        }

        // we need at least one original user class
        if (! $resolvedClassType instanceof UnionType) {
            return null;
        }

        foreach ($resolvedClassType->getTypes() as $unionedType) {
            if (! $unionedType instanceof ObjectType) {
                continue;
            }

            return $unionedType->getClassName();
        }

        return null;
    }

    private function matchClassTypeThatContainsConstant(Type $type, string $constant): ?string
    {
        if ($type instanceof ObjectType) {
            return $type->getClassName();
        }

        $classNames = TypeUtils::getDirectClassNames($type);

        foreach ($classNames as $className) {
            $currentClassConstants = $this->getConstantsDefinedInClass($className);
            if (! in_array($constant, $currentClassConstants, true)) {
                continue;
            }

            return $className;
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getConstantsDefinedInClass(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);

        $constants = $reflectionClass->getConstants();

        $currentClassConstants = array_keys($constants);
        $parentClassReflection = $reflectionClass->getParentClass();

        if (! $parentClassReflection) {
            return $currentClassConstants;
        }

        $parentClassConstants = array_keys($constants);
        return array_diff($currentClassConstants, $parentClassConstants);
    }
}
