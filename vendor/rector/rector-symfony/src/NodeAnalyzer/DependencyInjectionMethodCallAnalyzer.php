<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
final class DependencyInjectionMethodCallAnalyzer
{
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver
     */
    private $serviceTypeMethodCallResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->propertyNaming = $propertyNaming;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->betterNodeFinder = $betterNodeFinder;
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
        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $serviceType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
