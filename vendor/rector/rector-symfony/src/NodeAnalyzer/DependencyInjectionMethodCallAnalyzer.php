<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyNaming = $propertyNaming;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function replaceMethodCallWithPropertyFetchAndDependency(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $serviceType = $this->serviceTypeMethodCallResolver->resolve($methodCall);
        if (!$serviceType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $classLike = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $serviceType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($classLike, $propertyMetadata);
        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }
}
