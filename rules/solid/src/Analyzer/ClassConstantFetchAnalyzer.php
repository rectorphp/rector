<?php

declare(strict_types=1);

namespace Rector\SOLID\Analyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Contract\Rector\PostRectorInterface;

final class ClassConstantFetchAnalyzer
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

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    /**
     * This is needed to run BEFORE process of nodes,
     * because @see PostRectorInterface can modify nodes and names of class constants
     *
     * @todo this should be resolved on constant adding
     */
    public function warmup(): void
    {
        if ($this->classConstantFetchByClassAndName !== [] && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $classConstFetches = $this->parsedNodeCollector->getNodesByType(ClassConstFetch::class);
        foreach ($classConstFetches as $classConstantFetch) {
            $this->addClassConstantFetch($classConstantFetch);
        }
    }

    /**
     * Returns class constant usages for the declaring class name and constant name
     * @return string[][][]
     */
    public function provideClassConstantFetchByClassAndName(): array
    {
        return $this->classConstantFetchByClassAndName;
    }

    private function addClassConstantFetch(ClassConstFetch $classConstFetch): void
    {
        $constantName = $this->nodeNameResolver->getName($classConstFetch->name);

        if ($constantName === 'class' || $constantName === null) {
            // this is not a manual constant
            return;
        }

        $resolvedClassType = $this->nodeTypeResolver->resolve($classConstFetch->class);

        $className = $this->resolveClassTypeThatContainsConstantOrFirstUnioned($resolvedClassType, $constantName);
        if ($className === null) {
            return;
        }

        // current class
        $classOfUse = $classConstFetch->getAttribute(AttributeKey::CLASS_NAME);

        $this->classConstantFetchByClassAndName[$className][$constantName][] = $classOfUse;

        $this->classConstantFetchByClassAndName[$className][$constantName] = array_unique(
            $this->classConstantFetchByClassAndName[$className][$constantName]
        );
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
        if ($resolvedClassType instanceof UnionType) {
            foreach ($resolvedClassType->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                return $unionedType->getClassName();
            }
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
            $classOrInterface = $this->parsedNodeCollector->findClassOrInterface($className);
            if ($classOrInterface === null) {
                continue;
            }

            foreach ($classOrInterface->getConstants() as $classConstant) {
                if ($this->nodeNameResolver->isName($classConstant, $constant)) {
                    return $className;
                }
            }
        }

        return null;
    }
}
