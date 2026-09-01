<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Collectors\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202609\TomasVotruba\UnusedPublic\ClassTypeDetector;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<StaticCall, non-empty-array<string>|null>
 */
final class StaticMethodCallCollector implements Collector
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
        return StaticCall::class;
    }
    /**
     * @param StaticCall $node
     * @return non-empty-array<string>|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->shouldCollectMethods()) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (!$node->class instanceof Name) {
            return null;
        }
        // skip calls in tests, as they are not used in production
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $this->classTypeDetector->isTestClass($classReflection)) {
            return null;
        }
        return [$node->class->toString() . '::' . $node->name->toString()];
    }
}
