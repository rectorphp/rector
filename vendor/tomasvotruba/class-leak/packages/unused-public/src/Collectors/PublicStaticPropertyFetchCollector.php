<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202609\TomasVotruba\UnusedPublic\ClassTypeDetector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<StaticPropertyFetch, non-empty-array<string>|null>
 */
final class PublicStaticPropertyFetchCollector implements Collector
{
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @readonly
     */
    private ClassTypeDetector $classTypeDetector;
    public function __construct(Configuration $configuration, ClassTypeDetector $classTypeDetector)
    {
        $this->configuration = $configuration;
        $this->classTypeDetector = $classTypeDetector;
    }
    public function getNodeType(): string
    {
        return StaticPropertyFetch::class;
    }
    /**
     * @param StaticPropertyFetch $node
     * @return non-empty-array<string>|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->isUnusedPropertyEnabled()) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if ($node->class instanceof Name && ($node->class->toString() === 'self' || $node->class->toString() === 'static')) {
            // self fetch is allowed
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $this->classTypeDetector->isTestClass($classReflection)) {
            return null;
        }
        $classType = $node->class instanceof Name ? $scope->resolveTypeByName($node->class) : $scope->getType($node->class);
        $result = [];
        foreach ($classType->getObjectClassReflections() as $classReflection) {
            $propertyName = $node->name->toString();
            if (!$classReflection->hasProperty($propertyName)) {
                continue;
            }
            $propertyReflection = $classReflection->getProperty($propertyName, $scope);
            $result[] = $propertyReflection->getDeclaringClass()->getName() . '::' . $propertyName;
        }
        if ($result === []) {
            return null;
        }
        return $result;
    }
}
