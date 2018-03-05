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

//        $constructorMethod = $classNode->getMethod('__construct');
//
//        /** @var ClassMethod $constructorMethod */
//        if ($constructorMethod) {
//            $this->addVariableAssignToMethod($parameterVariableInfo, $constructorMethod, $propertyAssignNode);
//            return;
//        }
//
//        $constructorMethod = $this->createMethodWithPropertyAndAssign($parameterVariableInfo, $propertyAssignNode);
//        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
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

        $constructorMethod = $this->createClassMethodNodeWithPropertyAndAssign($variableInfo, $assignNode);
        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod);
    }

    private function createClassMethodNodeWithPropertyAndAssign(
        VariableInfo $variableInfo,
        Expression $expressionNode
    ): ClassMethod {
        return $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($variableInfo))
            ->addStmts([$expressionNode])
            ->getNode();
    }

    private function createParameter(VariableInfo $variableInfo): Param
    {
        $paramBuild = $this->builderFactory->param($variableInfo->getName());

        foreach ($variableInfo->getTypes() as $type) {
            $paramBuild->setTypeHint($this->nodeFactory->createTypeName($type));
        }

        return $paramBuild;
    }

    private function addVariableAssignToMethod(
        VariableInfo $variableInfo,
        ClassMethod $classMethodNode,
        $propertyAssignNode
    ): void {
        // has parameter already?
        foreach ($classMethodNode->params as $constructorParameter) {
            if ($constructorParameter->var->name === $variableInfo->getName()) {
                return;
            }
        }

        $classMethodNode->params[] = $this->createParameter($variableInfo)
            ->getNode();

        $classMethodNode->stmts[] = $propertyAssignNode;
    }
}
