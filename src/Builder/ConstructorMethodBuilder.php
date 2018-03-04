<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Builder\Class_\VariableInfo;
use Rector\Node\NodeFactory;

final class ConstructorMethodBuilder
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(BuilderFactory $builderFactory, StatementGlue $statementGlue, NodeFactory $nodeFactory)
    {
        $this->builderFactory = $builderFactory;
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
    }

    public function addPropertyWithExpression(
        Class_ $classNode,
        VariableInfo $argument,
        Expr $exprNode,
        VariableInfo $assignProperty
    ): void {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignmentWithExpr($assignProperty->getName(), $exprNode);

        $constructorMethod = $classNode->getMethod('__construct') ?: null;
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            // has parameter already?
            foreach ($constructorMethod->params as $constructorParameter) {
                if ($constructorParameter->var->name === $argument->getName()) {
                    return;
                }
            }

            $constructorMethod->params[] = $this->createParameter($argument->getTypes(), $argument->getName())
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        $constructorMethod = $this->createMethodWithPropertyAndAssign('__construct', $argument, $propertyAssignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    public function addPropertyAssignToClass(Class_ $classNode, VariableInfo $property): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($property->getName());

        $constructorMethod = $classNode->getMethod('__construct') ?: null;
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            // has parameter already?
            foreach ($constructorMethod->params as $constructorParameter) {
                if ($constructorParameter->var->name === $property->getName()) {
                    return;
                }
            }

            $constructorMethod->params[] = $this->createParameter($property->getTypes(), $property->getName())
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        $constructorMethod = $this->createMethodWithPropertyAndAssign('__construct', $property, $propertyAssignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    /**
     * @param string[] $propertyTypes
     */
    private function createParameter(array $propertyTypes, string $propertyName): Param
    {
        $paramBuild = $this->builderFactory->param($propertyName);
        foreach ($propertyTypes as $propertyType) {
            $paramBuild->setTypeHint($this->nodeFactory->createTypeName($propertyType));
        }

        return $paramBuild;
    }

    private function createMethodWithPropertyAndAssign(string $name, VariableInfo $variable, Expression $expressionNode): Method
    {
        return $this->builderFactory->method($name)
            ->makePublic()
            ->addParam($this->createParameter($variable->getTypes(), $variable->getName()))
            ->addStmts([$expressionNode]);
    }
}
