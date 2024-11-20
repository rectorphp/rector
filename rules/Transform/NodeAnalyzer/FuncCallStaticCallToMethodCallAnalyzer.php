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
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
final class FuncCallStaticCallToMethodCallAnalyzer
{
    /**
     * @readonly
     */
    private TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private PropertyFetchFactory $propertyFetchFactory;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    public function __construct(TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver, PropertyNaming $propertyNaming, NodeNameResolver $nodeNameResolver, NodeFactory $nodeFactory, PropertyFetchFactory $propertyFetchFactory, ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
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
        $this->classDependencyManipulator->addConstructorDependency($class, new PropertyMetadata($propertyName, $objectType));
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
