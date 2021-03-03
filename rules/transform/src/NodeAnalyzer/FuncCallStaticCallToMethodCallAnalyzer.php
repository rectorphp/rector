<?php

declare(strict_types=1);

namespace Rector\Transform\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\DependencyInjection\PropertyAdder;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;

final class FuncCallStaticCallToMethodCallAnalyzer
{
    /**
     * @var TypeProvidingExprFromClassResolver
     */
    private $typeProvidingExprFromClassResolver;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var PropertyFetchFactory
     */
    private $propertyFetchFactory;

    /**
     * @var PropertyAdder
     */
    private $propertyAdder;

    public function __construct(
        TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver,
        PropertyNaming $propertyNaming,
        NodeNameResolver $nodeNameResolver,
        NodeFactory $nodeFactory,
        PropertyFetchFactory $propertyFetchFactory,
        PropertyAdder $propertyAdder
    ) {
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeFactory = $nodeFactory;
        $this->propertyFetchFactory = $propertyFetchFactory;
        $this->propertyAdder = $propertyAdder;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @return MethodCall|PropertyFetch|Variable
     */
    public function matchTypeProvidingExpr(Class_ $class, FunctionLike $functionLike, ObjectType $objectType): Expr
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass(
            $class,
            $functionLike,
            $objectType
        );
        if ($expr !== null) {
            if ($expr instanceof Variable) {
                $this->addClassMethodParamForVariable($expr, $objectType, $functionLike);
            }

            return $expr;
        }

        $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
        $this->propertyAdder->addConstructorDependencyToClass($class, $objectType, $propertyName);
        return $this->propertyFetchFactory->createFromType($objectType);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function addClassMethodParamForVariable(
        Variable $variable,
        ObjectType $objectType,
        FunctionLike $functionLike
    ): void {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);

        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
