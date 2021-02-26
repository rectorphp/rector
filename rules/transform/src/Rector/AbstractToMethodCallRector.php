<?php

declare(strict_types=1);

namespace Rector\Transform\Rector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Transform\NodeFactory\PropertyFetchFactory;
use Rector\Transform\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;

abstract class AbstractToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var TypeProvidingExprFromClassResolver
     */
    private $typeProvidingExprFromClassResolver;

    /**
     * @var PropertyFetchFactory
     */
    private $propertyFetchFactory;

    /**
     * @required
     */
    public function autowireAbstractToMethodCallRector(
        PropertyNaming $propertyNaming,
        TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver,
        PropertyFetchFactory $propertyFetchFactory
    ): void {
        $this->propertyNaming = $propertyNaming;
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
        $this->propertyFetchFactory = $propertyFetchFactory;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @return MethodCall|PropertyFetch|Variable
     */
    protected function matchTypeProvidingExpr(Class_ $class, FunctionLike $functionLike, ObjectType $objectType): Expr
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
        $this->addConstructorDependencyToClass($class, $objectType, $propertyName);
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
        $variableName = $this->getName($variable);

        // add variable to __construct as dependency
        $functionLike->params[] = $this->nodeFactory->createParamFromNameAndType($variableName, $objectType);
    }
}
