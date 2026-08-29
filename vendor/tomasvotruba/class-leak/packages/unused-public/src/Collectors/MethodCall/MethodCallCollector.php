<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202608\TomasVotruba\UnusedPublic\CallReferece\CallReferencesFlatter;
use RectorPrefix202608\TomasVotruba\UnusedPublic\CallReferece\ParentCallReferenceResolver;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ClassMethodCallReferenceResolver;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ClassTypeDetector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<MethodCall, non-empty-array<string>|null>
 */
final class MethodCallCollector implements Collector
{
    /**
     * @readonly
     */
    private ParentCallReferenceResolver $parentCallReferenceResolver;
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
    public function __construct(ParentCallReferenceResolver $parentCallReferenceResolver, ClassMethodCallReferenceResolver $classMethodCallReferenceResolver, Configuration $configuration, ClassTypeDetector $classTypeDetector, CallReferencesFlatter $callReferencesFlatter)
    {
        $this->parentCallReferenceResolver = $parentCallReferenceResolver;
        $this->classMethodCallReferenceResolver = $classMethodCallReferenceResolver;
        $this->configuration = $configuration;
        $this->classTypeDetector = $classTypeDetector;
        $this->callReferencesFlatter = $callReferencesFlatter;
    }
    public function getNodeType(): string
    {
        return MethodCall::class;
    }
    /**
     * @param MethodCall $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->shouldCollectMethods()) {
            return null;
        }
        // unable to resolve method name
        if ($node->name instanceof Expr) {
            return null;
        }
        // skip calls in tests, as they are not used in production
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $this->classTypeDetector->isTestClass($classReflection)) {
            return null;
        }
        $classMethodCallReferences = $this->classMethodCallReferenceResolver->resolve($node, $scope);
        $classMethodReferences = $this->callReferencesFlatter->flatten($classMethodCallReferences);
        foreach ($classMethodCallReferences as $classMethodCallReference) {
            $parentClassMethodReferences = $this->parentCallReferenceResolver->findParentClassMethodReferences($classMethodCallReference->getClass(), $classMethodCallReference->getMethod());
            foreach ($parentClassMethodReferences as $parentClassMethodReference) {
                $classMethodReferences[] = $parentClassMethodReference;
            }
        }
        return $classMethodReferences;
    }
}
