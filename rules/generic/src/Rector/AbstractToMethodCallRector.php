<?php

declare(strict_types=1);

namespace Rector\Generic\Rector;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
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
     * @return MethodCall|PropertyFetch
     */
    protected function matchTypeProvidingExpr(Class_ $class, string $type): Expr
    {
        $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass($class, $type);
        if ($expr !== null) {
            return $expr;
        }

        $this->addPropertyTypeToClass($type, $class);
        return $this->createPropertyFetchFromClass($type);
    }

    private function addPropertyTypeToClass(string $type, Class_ $class): void
    {
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($type);
        $propertyName = $this->propertyNaming->fqnToVariableName($fullyQualifiedObjectType);
        $this->addPropertyToClass($class, $fullyQualifiedObjectType, $propertyName);
    }

    private function createPropertyFetchFromClass(string $type): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($type);

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
