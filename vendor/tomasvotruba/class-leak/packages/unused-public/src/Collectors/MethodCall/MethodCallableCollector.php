<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\MethodCallableNode;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202608\TomasVotruba\UnusedPublic\CallReferece\CallReferencesFlatter;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ClassMethodCallReferenceResolver;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ClassTypeDetector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<MethodCallableNode, non-empty-array<string>|null>
 */
final class MethodCallableCollector implements Collector
{
    /**
     * @readonly
     */
    private ClassMethodCallReferenceResolver $classMethodCallReferenceResolver;
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @readonly
     */
    private ClassTypeDetector $classTypeDetector;
    /**
     * @readonly
     */
    private CallReferencesFlatter $callReferencesFlatter;
    public function __construct(ClassMethodCallReferenceResolver $classMethodCallReferenceResolver, Configuration $configuration, ClassTypeDetector $classTypeDetector, CallReferencesFlatter $callReferencesFlatter)
    {
        $this->classMethodCallReferenceResolver = $classMethodCallReferenceResolver;
        $this->configuration = $configuration;
        $this->classTypeDetector = $classTypeDetector;
        $this->callReferencesFlatter = $callReferencesFlatter;
    }
    public function getNodeType(): string
    {
        return MethodCallableNode::class;
    }
    /**
     * @param MethodCallableNode $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->shouldCollectMethods()) {
            return null;
        }
        // unable to resolve method name
        if ($node->getName() instanceof Expr) {
            return null;
        }
        // skip calls in tests, as they are not used in production
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $this->classTypeDetector->isTestClass($classReflection)) {
            return null;
        }
        $classMethodCallReferences = $this->classMethodCallReferenceResolver->resolve($node->getOriginalNode(), $scope);
        return $this->callReferencesFlatter->flatten($classMethodCallReferences);
    }
}
