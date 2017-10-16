<?php declare(strict_types=1);

namespace Rector\Builder;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\NodeFactory;

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
        string $operation,
        string $argumentName
    ): void {
        $methodNode = $this->buildMethodNode($methodName, $propertyType, $propertyName, $operation, $argumentName);

        $this->statementGlue->addAsFirstMethod($classNode, $methodNode);
    }

    private function buildMethodNode(
        string $methodName,
        ?string $propertyType,
        string $propertyName,
        string $operation,
        string $argumentName
    ): ClassMethod {
        $methodBuild = $this->builderFactory->method($methodName)
            ->makePublic();

        $methodBodyStatement = $this->buildMethodBody($propertyName, $operation, $argumentName);

        if ($methodBodyStatement) {
            $methodBuild->addStmt($methodBodyStatement);
        }

        if ($propertyType && $operation === 'get') {
            $typeHint = Strings::endsWith($propertyType, '[]') ? 'array' : $propertyType;
            $methodBuild->setReturnType(new Identifier($typeHint));
        }

        if ($operation === 'add' || $operation === 'set') {
            $param = $this->builderFactory->param($argumentName);
            if ($propertyType) {
                $typeHint = Strings::endsWith($propertyType, '[]') ? 'array' : $propertyType;
                $param->setTypeHint($typeHint);
            }

            $methodBuild->addParam($param);
        }

        return $methodBuild->getNode();
    }

    private function buildMethodBody(string $propertyName, string $operation, string $argumentName): ?Stmt
    {
        if ($operation === 'set') {
            return $this->nodeFactory->createPropertyAssignment($propertyName);
        }

        if ($operation === 'add') {
            return $this->nodeFactory->createPropertyArrayAssignment($propertyName, $argumentName);
        }

        if (in_array($operation, ['get', 'is'], true)) {
            $propertyFetchNode = $this->nodeFactory->createLocalPropertyFetch($propertyName);

            return new Return_($propertyFetchNode);
        }

        return null;
    }
}
