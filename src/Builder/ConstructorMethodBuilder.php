<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\Property;
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

    public function addPropertyAssignToClass(Class_ $classNode, Property $property): void
    {
        $constructorMethod = $classNode->getMethod('__construct') ?: null;

        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($property->getName());

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

        /** @var Method $constructorMethod */
        $constructorMethod = $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($property->getTypes(), $property->getName()))
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
            $paramBuild->setTypeHint($this->nodeFactory->createTypeNamespace($propertyType));
        }

        return $paramBuild;
    }
}
