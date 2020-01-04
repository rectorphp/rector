<?php

declare(strict_types=1);

namespace Rector\SOLID\Analyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
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

        foreach ($this->parsedNodesByType->getNodesByType(ClassConstFetch::class) as $classConstantFetch) {
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

        $className = $this->matchClassTypeThatContainsConstant($resolvedClassType, $constantName);
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

    private function matchClassTypeThatContainsConstant(Type $type, string $constant): ?string
    {
        if ($type instanceof ObjectType) {
            return $type->getClassName();
        }

        $classNames = TypeUtils::getDirectClassNames($type);

        foreach ($classNames as $className) {
            $classOrInterface = $this->parsedNodesByType->findClassOrInterface($className);
            if ($classOrInterface === null) {
                continue;
            }

            foreach ($classOrInterface->getConstants() as $classConstant) {
                if ($this->nameResolver->isName($classConstant, $constant)) {
                    return $className;
                }
            }
        }

        return null;
    }
}
