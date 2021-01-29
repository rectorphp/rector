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
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
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
        return $this->propertyFetchFactory->createFromType($type);
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
}
