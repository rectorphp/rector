<?php

declare (strict_types=1);
namespace Rector\Transform\NodeAnalyzer;

use PhpParser\Node\Expr;
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
    public function __construct(TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver, PropertyNaming $propertyNaming, NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory, PropertyFetchFactory $propertyFetchFactory, PropertyToAddCollector $propertyToAddCollector)
    {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\Variable
     */
    public function matchTypeProvidingExpr(Class_ $class, ClassMethod $classMethod, ObjectType $objectType)
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass($class, $classMethod, $objectType);
        if ($expr instanceof Expr) {
            if ($expr instanceof Variable) {
                $this->addClassMethodParamForVariable($expr, $objectType, $classMethod);
            }
            return $expr;
        }
        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $propertyMetadata = new PropertyMetadata($propertyName, $objectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $this->propertyFetchFactory->createFromType($objectType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function addClassMethodParamForVariable(Variable $variable, ObjectType $objectType, $functionLike) : void
    {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);
        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
