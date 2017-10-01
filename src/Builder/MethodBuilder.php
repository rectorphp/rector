<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt;
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
        string $propertyName,
        string $operation
    ): void {
        $methodNode = $this->buildMethodNode($methodName, $propertyType, $propertyName, $operation);

        $this->statementGlue->addAsFirstMethod($classNode, $methodNode);
    }

    private function buildMethodNode(
        string $methodName,
        ?string $propertyType,
        string $propertyName,
        string $operation
    ): ClassMethod {
        $methodBuild = $this->builderFactory->method($methodName)
            ->makePublic();

        $methodBodyStatement = $this->buildMethodBody($propertyName, $operation);
        if ($methodBodyStatement) {
            $methodBuild->addStmt($methodBodyStatement);
        }

        return $methodBuild->getNode();
    }

    private function buildMethodBody(string $propertyName, string $operation): ?Stmt
    {
        if ($operation === 'set') {
            return $this->nodeFactory->createPropertyAssignment($propertyName);
        }

        if ($operation === 'get') {
            $propertyFetchNode = $this->nodeFactory->createLocalPropertyFetch($propertyName);

            return new Return_($propertyFetchNode);
        }

        if ($operation === 'add') {
            return $this->nodeFactory->createPropertyArrayAssignment($propertyName);
        }

        return null;
    }
}
