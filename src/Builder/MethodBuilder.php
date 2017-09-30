<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeFactory\NodeFactory;

final class MethodBuilder
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

    public function addMethodToClass(
        Class_ $classNode,
        string $methodName,
        ?string $propertyType,
        string $propertyName
    ): void {
        $methodNode = $this->buildMethodNode($methodName, $propertyType, $propertyName);

        $this->statementGlue->addAsFirstMethod($classNode, $methodNode);
    }

    private function buildMethodNode(string $methodName, ?string $propertyType, string $propertyName): ClassMethod
    {
        $propertyFetchNode = $this->nodeFactory->createLocalPropertyFetch($propertyName);

        $returnPropertyNode = new Return_($propertyFetchNode);

        return $this->builderFactory->method($methodName)
            ->makePublic()
            ->addStmt($returnPropertyNode)
            ->getNode();
    }
}
