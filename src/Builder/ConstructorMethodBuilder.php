<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
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

    public function __construct(
        BuilderFactory $builderFactory,
        StatementGlue $statementGlue,
        NodeFactory $nodeFactory
    ) {
        $this->builderFactory = $builderFactory;
        $this->statementGlue = $statementGlue;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param string[] $propertyTypes
     */
    public function addPropertyAssignToClass(Class_ $classNode, array $propertyTypes, string $propertyName): void
    {
        $constructorMethod = $classNode->getMethod('__construct') ?: null;

        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($propertyName);

        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            // has parameter already?
            foreach ($constructorMethod->params as $constructorParameter) {
                if ($constructorParameter->var->name === $propertyName) {
                    return;
                }
            }

            $constructorMethod->params[] = $this->createParameter($propertyTypes, $propertyName)
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        /** @var Method $constructorMethod */
        $constructorMethod = $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($propertyTypes, $propertyName))
            ->addStmts([$propertyAssignNode]);

        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    /**
     * @param string[] $propertyTypes
     */
    private function createParameter(array $propertyTypes, string $propertyName): Param
    {
        $paramBuild = $this->builderFactory->param($propertyName);
        foreach ($propertyTypes as $propertyType) {
            $paramBuild->setTypeHint($propertyType);
        }

        return $paramBuild;
    }
}
