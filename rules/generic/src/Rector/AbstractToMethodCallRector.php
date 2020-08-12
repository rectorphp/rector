<?php

declare(strict_types=1);

namespace Rector\Generic\Rector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

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
     * @required
     */
    public function autowireAbstractToMethodCallRector(
        PropertyNaming $propertyNaming,
        TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver
    ): void {
        $this->propertyNaming = $propertyNaming;
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @return MethodCall|PropertyFetch|Variable
     */
    protected function matchTypeProvidingExpr(Class_ $class, FunctionLike $functionLike, string $type): Expr
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass(
            $class,
            $functionLike,
            $type
        );
        if ($expr !== null) {
            if ($expr instanceof Variable) {
                $this->addClassMethodParamForVariable($expr, $type, $functionLike);
            }

            return $expr;
        }

        $this->addPropertyTypeToClass($type, $class);
        return $this->createPropertyFetchFromClass($type);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function addClassMethodParamForVariable(Variable $variable, string $type, FunctionLike $functionLike): void
    {
        /** @var string $variableName */
        $variableName = $this->getName($variable);

        // add variable to __construct as dependency
        $param = $this->nodeFactory->createParamFromNameAndType($variableName, new FullyQualifiedObjectType($type));

        $functionLike->params[] = $param;
    }

    private function addPropertyTypeToClass(string $type, Class_ $class): void
    {
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($type);
        $propertyName = $this->propertyNaming->fqnToVariableName($fullyQualifiedObjectType);
        $this->addConstructorDependencyToClass($class, $fullyQualifiedObjectType, $propertyName);
    }

    private function createPropertyFetchFromClass(string $type): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
