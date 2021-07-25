<?php

declare(strict_types=1);

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
    public function __construct(
        private TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver,
        private PropertyNaming $propertyNaming,
        private NodeNameResolver $nodeNameResolver,
        private NodeFactory $nodeFactory,
        private PropertyFetchFactory $propertyFetchFactory,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function matchTypeProvidingExpr(
        Class_ $class,
        ClassMethod | Function_ $functionLike,
        ObjectType $objectType
    ): MethodCall | PropertyFetch | Variable {
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
        $propertyMetadata = new PropertyMetadata($propertyName, $objectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);

        return $this->propertyFetchFactory->createFromType($objectType);
    }

    private function addClassMethodParamForVariable(
        Variable $variable,
        ObjectType $objectType,
        ClassMethod | Function_ $functionLike
    ): void {
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);

        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
