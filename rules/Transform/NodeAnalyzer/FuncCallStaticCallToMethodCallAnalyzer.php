<?php

declare (strict_types=1);
namespace Rector\Transform\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
final class FuncCallStaticCallToMethodCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver
     */
    private $typeProvidingExprFromClassResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Transform\NodeFactory\PropertyFetchFactory
     */
    private $propertyFetchFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(\Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Transform\NodeFactory\PropertyFetchFactory $propertyFetchFactory, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable
     */
    public function matchTypeProvidingExpr(\PhpParser\Node\Stmt\Class_ $class, $functionLike, \PHPStan\Type\ObjectType $objectType)
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass($class, $functionLike, $objectType);
        if ($expr !== null) {
            if ($expr instanceof \PhpParser\Node\Expr\Variable) {
                $this->addClassMethodParamForVariable($expr, $objectType, $functionLike);
            }
            return $expr;
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyMetadata = new \Rector\PostRector\ValueObject\PropertyMetadata($propertyName, $objectType, \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->propertyFetchFactory->createFromType($objectType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function addClassMethodParamForVariable(\PhpParser\Node\Expr\Variable $variable, \PHPStan\Type\ObjectType $objectType, $functionLike) : void
    {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);
        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
