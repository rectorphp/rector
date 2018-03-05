<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
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
        VariableInfo $parameterVariableInfo,
        Expr $exprNode,
        VariableInfo $propertyVariableInfo
    ): void {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignmentWithExpr(
            $propertyVariableInfo->getName(),
            $exprNode
        );

        $constructorMethod = $classNode->getMethod('__construct') ?: null;
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            // has parameter already?
            foreach ($constructorMethod->params as $constructorParameter) {
                if ($constructorParameter->var->name === $parameterVariableInfo->getName()) {
                    return;
                }
            }

            $constructorMethod->params[] = $this->createParameter($parameterVariableInfo)
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        $constructorMethod = $this->createMethodWithPropertyAndAssign($propertyVariableInfo, $propertyAssignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    public function addPropertyAssignToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($variableInfo->getName());

        $constructorMethod = $classNode->getMethod('__construct') ?: null;
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            // has parameter already?
            foreach ($constructorMethod->params as $constructorParameter) {
                if ($constructorParameter->var->name === $variableInfo->getName()) {
                    return;
                }
            }

            $constructorMethod->params[] = $this->createParameter($variableInfo)
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        $constructorMethod = $this->createMethodWithPropertyAndAssign($variableInfo, $propertyAssignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    private function createMethodWithPropertyAndAssign(VariableInfo $variableInfo, Expression $expressionNode): Method
    {
        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($variableInfo))
            ->addStmts([$expressionNode]);
    }

    private function createParameter(VariableInfo $variableInfo): Param
    {
        $paramBuild = $this->builderFactory->param($variableInfo->getName());
        foreach ($variableInfo->getTypes() as $type) {
            $paramBuild->setTypeHint($this->nodeFactory->createTypeName($type));
        }

        return $paramBuild;
    }
}
