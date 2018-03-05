<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
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

    /**
     * Creates:
     * public function __construct($someProperty)
     * {
     *      $this->someProperty = $someProperty;
     * }
     */
    public function addSimplePropertyAssignToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($variableInfo->getName());
        $this->addParameterAndAssignToConstructorArgumentsOfClass($classNode, $variableInfo, $propertyAssignNode);
    }

    public function addParameterAndAssignToConstructorArgumentsOfClass(
        Class_ $classNode,
        VariableInfo $variableInfo,
        Expression $assignNode
    ): void {
        $constructorMethod = $classNode->getMethod('__construct');
        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assignNode);
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

    private function addParameterAndAssignToMethod(
        ClassMethod $classMethodNode,
        VariableInfo $variableInfo,
        Expression $propertyAssignNode
    ): void {
        if ($this->hasMethodParameter($classMethodNode, $variableInfo)) {
            return;
        }

        $classMethodNode->params[] = $this->nodeFactory->createParamFromVariableInfo($variableInfo);
        $classMethodNode->stmts[] = $propertyAssignNode;
    }

    private function hasMethodParameter(ClassMethod $classMethodNode, VariableInfo $variableInfo): bool
    {
        foreach ($classMethodNode->params as $constructorParameter) {
            if ($constructorParameter->var->name === $variableInfo->getName()) {
                return true;
            }
        }

        return false;
    }
}
