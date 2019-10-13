<?php

declare(strict_types=1);

namespace Rector\SOLID\Analyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\Testing\PHPUnit\PHPUnitEnvironment;

final class ClassConstantFetchAnalyzer
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var string[][][]
     */
    private $classConstantFetchByClassAndName = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
    }

    /**
     * Returns class constant usages for the declaring class name and constant name
     * @return string[][][]
     */
    public function provideClassConstantFetchByClassAndName(): array
    {
        if ($this->classConstantFetchByClassAndName !== [] && ! PHPUnitEnvironment::isPHPUnitRun()) {
            return $this->classConstantFetchByClassAndName;
        }

        foreach ($this->parsedNodesByType->getClassConstantFetches() as $classConstantFetch) {
            $this->addClassConstantFetch($classConstantFetch);
        }

        return $this->classConstantFetchByClassAndName;
    }

    private function addClassConstantFetch(ClassConstFetch $classConstFetch): void
    {
        $constantName = $this->nameResolver->getName($classConstFetch->name);

        if ($constantName === 'class' || $constantName === null) {
            // this is not a manual constant
            return;
        }

        $resolvedClassType = $this->nodeTypeResolver->resolve($classConstFetch->class);

        $classType = $this->matchClassTypeThatContainsConstant($resolvedClassType, $constantName);
        if ($classType === null) {
            return;
        }

        // current class
        $classOfUse = $classConstFetch->getAttribute(AttributeKey::CLASS_NAME);

        $this->classConstantFetchByClassAndName[$classType->getClassName()][$constantName][] = $classOfUse;

        $this->classConstantFetchByClassAndName[$classType->getClassName()][$constantName] = array_unique(
            $this->classConstantFetchByClassAndName[$classType->getClassName()][$constantName]
        );
    }

    private function matchClassTypeThatContainsConstant(?Type $type, string $constant): ?ObjectType
    {
        if ($type instanceof ObjectType) {
            return $type;
        }

        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                $classOrInterface = $this->parsedNodesByType->findClassOrInterface($unionedType->getClassName());
                if ($classOrInterface === null) {
                    continue;
                }

                foreach ($classOrInterface->getConstants() as $classConstant) {
                    if ($this->nameResolver->isName($classConstant, $constant)) {
                        return $unionedType;
                    }
                }
            }
        }

        return null;
    }
}
