<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class DependencyInjectionMethodCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver
     */
    private $serviceTypeMethodCallResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver $promotedPropertyResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator)
    {
        $this->propertyNaming = $propertyNaming;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyManipulator = $propertyManipulator;
    }
    public function replaceMethodCallWithPropertyFetchAndDependency(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr\PropertyFetch
    {
        $serviceType = $this->serviceTypeMethodCallResolver->resolve($methodCall);
        if (!$serviceType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($methodCall, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $resolvedPropertyNameByType = $this->propertyManipulator->resolveExistingClassPropertyNameByType($class, $serviceType);
        if (\is_string($resolvedPropertyNameByType)) {
            $propertyName = $resolvedPropertyNameByType;
        } else {
            $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
            $propertyName = $this->resolveNewPropertyNameWhenExists($class, $propertyName, $propertyName);
        }
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $serviceType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
    private function resolveNewPropertyNameWhenExists(\PhpParser\Node\Stmt\Class_ $class, string $originalPropertyName, string $propertyName, int $count = 1) : string
    {
        $lastCount = \substr($propertyName, \strlen($originalPropertyName));
        if (\is_numeric($lastCount)) {
            $count = (int) $lastCount;
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            if ($this->nodeNameResolver->isName($promotedPropertyParam->var, $propertyName)) {
                $propertyName = $this->resolveIncrementPropertyName($originalPropertyName, $count);
                return $this->resolveNewPropertyNameWhenExists($class, $originalPropertyName, $propertyName, $count);
            }
        }
        $property = $class->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return $propertyName;
        }
        $propertyName = $this->resolveIncrementPropertyName($originalPropertyName, $count);
        return $this->resolveNewPropertyNameWhenExists($class, $originalPropertyName, $propertyName, $count);
    }
    private function resolveIncrementPropertyName(string $originalPropertyName, int $count) : string
    {
        ++$count;
        return $originalPropertyName . $count;
    }
}
