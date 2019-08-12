<?php declare(strict_types=1);

namespace Rector\SOLID\Analyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
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

        $className = $this->nameResolver->getName($classConstFetch->class);

        if (in_array($className, ['static', 'self', 'parent'], true)) {
            $resolvedClassTypes = $this->nodeTypeResolver->resolve($classConstFetch->class);

            $className = $this->matchClassTypeThatContainsConstant($resolvedClassTypes, $constantName);
            if ($className === null) {
                return;
            }
        } else {
            $resolvedClassTypes = $this->nodeTypeResolver->resolve($classConstFetch->class);
            $className = $this->matchClassTypeThatContainsConstant($resolvedClassTypes, $constantName);

            if ($className === null) {
                return;
            }
        }

        // current class
        $classOfUse = $classConstFetch->getAttribute(AttributeKey::CLASS_NAME);

        $this->classConstantFetchByClassAndName[$className][$constantName][] = $classOfUse;

        $this->classConstantFetchByClassAndName[$className][$constantName] = array_unique(
            $this->classConstantFetchByClassAndName[$className][$constantName]
        );
    }

    /**
     * @param string[] $resolvedClassTypes
     */
    private function matchClassTypeThatContainsConstant(array $resolvedClassTypes, string $constant): ?string
    {
        if (count($resolvedClassTypes) === 1) {
            return $resolvedClassTypes[0];
        }

        foreach ($resolvedClassTypes as $resolvedClassType) {
            $classOrInterface = $this->parsedNodesByType->findClassOrInterface($resolvedClassType);
            if ($classOrInterface === null) {
                continue;
            }

            foreach ($classOrInterface->stmts as $stmt) {
                if (! $stmt instanceof ClassConst) {
                    continue;
                }

                if ($this->nameResolver->isName($stmt, $constant)) {
                    return $resolvedClassType;
                }
            }
        }

        return null;
    }
}
