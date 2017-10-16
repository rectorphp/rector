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

    public function addPropertyAssignToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $constructorMethod = $classNode->getMethod('__construct') ?: null;

        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($propertyName);

        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            $constructorMethod->params[] = $this->createParameter($propertyType, $propertyName)
                ->getNode();

            $constructorMethod->stmts[] = $propertyAssignNode;

            return;
        }

        /** @var Method $constructorMethod */
        $constructorMethod = $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($propertyType, $propertyName))
            ->addStmts([$propertyAssignNode]);

        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    private function createParameter(string $propertyType, string $propertyName): Param
    {
        return $this->builderFactory->param($propertyName)
            ->setTypeHint($propertyType);
    }
}
