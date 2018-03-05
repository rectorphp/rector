<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;
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

    public function addPropertyAssignWithExpression(
        Class_ $classNode,
        VariableInfo $parameterVariableInfo,
        Expr $exprNode,
        VariableInfo $propertyVariableInfo
    ): void {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignmentWithExpr(
            $propertyVariableInfo->getName(),
            $exprNode
        );

        $this->addVariableAndAssignToConstructorArgumentsOfClass($classNode, $parameterVariableInfo, $propertyAssignNode);
    }

    public function addPropertyAssignToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($variableInfo->getName());
        $this->addVariableAndAssignToConstructorArgumentsOfClass($classNode, $variableInfo, $propertyAssignNode);
    }

    private function addVariableAndAssignToConstructorArgumentsOfClass(
        Class_ $classNode,
        VariableInfo $variableInfo,
        Expression $assignNode
    ): void {
        $constructorMethod = $classNode->getMethod('__construct');
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            $this->addVariableAssignToMethod($variableInfo, $constructorMethod, $assignNode);
            return;
        }

        $constructorMethod = $this->createClassMethodNodeWithParameterAndAssign($variableInfo, $assignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod);
    }

    private function createClassMethodNodeWithParameterAndAssign(
        VariableInfo $variableInfo,
        Expression $expressionNode
    ): ClassMethod {
        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->nodeFactory->createParamFromVariableInfo($variableInfo))
            ->addStmts([$expressionNode])
            ->getNode();
    }

    private function addVariableAssignToMethod(
        VariableInfo $variableInfo,
        ClassMethod $classMethodNode,
        Expression $propertyAssignNode
    ): void {
        if ($this->hasMethodParameter($variableInfo, $classMethodNode)) {
            return;
        }

        $classMethodNode->params[] = $this->nodeFactory->createParamFromVariableInfo($variableInfo);
        $classMethodNode->stmts[] = $propertyAssignNode;
    }

    private function hasMethodParameter(VariableInfo $variableInfo, ClassMethod $classMethodNode): bool
    {
        foreach ($classMethodNode->params as $constructorParameter) {
            if ($constructorParameter->var->name === $variableInfo->getName()) {
                return true;
            }
        }

        return false;
    }
}
