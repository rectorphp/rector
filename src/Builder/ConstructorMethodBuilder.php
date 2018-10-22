<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Builder\Class_\VariableInfo;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\NameResolver;
use Rector\NodeTypeResolver\Application\ClassNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;

/**
 * Creates new constructor or adds new dependency to already existing one
 *
 * public function __construct($someProperty)
 * {
 *      $this->someProperty = $someProperty;
 * }
 */
final class ConstructorMethodBuilder
{
    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ClassNodeCollector
     */
    private $classNodeCollector;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        StatementGlue $statementGlue,
        NodeFactory $nodeFactory,
        ClassNodeCollector $classNodeCollector,
        NameResolver $nameResolver
    ) {
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
        $this->classNodeCollector = $classNodeCollector;
        $this->nameResolver = $nameResolver;
    }

    public function addSimplePropertyAssignToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($variableInfo->getName());
        $this->addConstructorDependency($classNode, $variableInfo, $propertyAssignNode);
    }

    public function addConstructorDependency(
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

        $constructorMethod = $this->nodeFactory->createPublicMethod('__construct');

        $this->completeParentConstructor($classNode, $constructorMethod);

        $this->addParameterAndAssignToMethod($constructorMethod, $variableInfo, $assignNode);

        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod);

        $this->completeChildConstructors($classNode, $constructorMethod);
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
            if ($this->nameResolver->isName($constructorParameter->var, $variableInfo->getName())) {
                return true;
            }
        }

        return false;
    }

    private function completeParentConstructor(Class_ $classNode, ClassMethod $constructorClassMethodNode): void
    {
        $parentClassName = (string) $classNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! $parentClassName) {
            return;
        }

        // not in analyzed scope, nothing we can do
        $parentClassNode = $this->classNodeCollector->findClass($parentClassName);
        if (! $parentClassNode) {
            return;
        }

        // iterate up?
        $firstParentConstructMethodNode = $this->findFirstParentConstructor($parentClassNode);
        if ($firstParentConstructMethodNode === null) {
            return;
        }

        if (! $firstParentConstructMethodNode->params) {
            return;
        }

        // replicate parent parameters
        $constructorClassMethodNode->params = array_merge(
            $firstParentConstructMethodNode->params,
            $constructorClassMethodNode->params
        );

        // add parent::__construct(...) call
        $parentConstructCallNode = new StaticCall(
            new Name('parent'),
            new Identifier('__construct'),
            $this->convertParamNodesToArgNodes($firstParentConstructMethodNode->params)
        );

        $constructorClassMethodNode->stmts[] = new Expression($parentConstructCallNode);
    }

    /**
     * @param Param[] $paramNodes
     * @return Arg[]
     */
    private function convertParamNodesToArgNodes(array $paramNodes): array
    {
        $argNodes = [];
        foreach ($paramNodes as $paramNode) {
            $argNodes[] = new Arg($paramNode->var);
        }

        return $argNodes;
    }

    private function completeChildConstructors(Class_ $classNode, ClassMethod $constructorClassMethod): void
    {
        $childClassNodes = $this->classNodeCollector->findChildrenOfClass($this->nameResolver->resolve($classNode));

        foreach ($childClassNodes as $childClassNode) {
            if (! $childClassNode->getMethod('__construct')) {
                continue;
            }

            /** @var ClassMethod $childClassConstructorMethodNode */
            $childClassConstructorMethodNode = $childClassNode->getMethod('__construct');

            // replicate parent parameters
            $childClassConstructorMethodNode->params = array_merge(
                $constructorClassMethod->params,
                $childClassConstructorMethodNode->params
            );

            // add parent::__construct(...) call
            $parentConstructCallNode = new StaticCall(
                new Name('parent'),
                new Identifier('__construct'),
                $this->convertParamNodesToArgNodes($constructorClassMethod->params)
            );

            $childClassConstructorMethodNode->stmts = array_merge(
                [new Expression($parentConstructCallNode)],
                $childClassConstructorMethodNode->stmts
            );
        }
    }

    private function findFirstParentConstructor(Class_ $classNode): ?ClassMethod
    {
        while ($classNode !== null) {
            $constructMethodNode = $classNode->getMethod('__construct');
            if ($constructMethodNode) {
                return $constructMethodNode;
            }

            /** @var string $parentClassName */
            $parentClassName = $classNode->getAttribute(Attribute::PARENT_CLASS_NAME);
            if (! $parentClassName) {
                return null;
            }

            $classNode = $this->classNodeCollector->findClass($parentClassName);
        }

        return null;
    }
}
