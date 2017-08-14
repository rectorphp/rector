<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Parser;

final class ConstructorMethodBuilder
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    public function __construct(Parser $parser, BuilderFactory $builderFactory, StatementGlue $statementGlue)
    {
        $this->parser = $parser;
        $this->builderFactory = $builderFactory;
        $this->statementGlue = $statementGlue;
    }

    public function addPropertyAssignToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $assign = $this->createPropertyAssignment($propertyName);

        $constructorMethod = $classNode->getMethod('__construct') ?: null;

        /** @var ClassMethod $constructorMethod */
        if ($constructorMethod) {
            $constructorMethod->params[] = $this->createParameter($propertyType, $propertyName)
                ->getNode();

            $constructorMethod->stmts[] = $assign[0];

            return;
        }

        /** @var Method $constructorMethod */
        $constructorMethod = $this->builderFactory->method('__construct')
            ->makePublic()
            ->addParam($this->createParameter($propertyType, $propertyName))
            ->addStmts($assign);

        $this->statementGlue->addAsFirstMethod($classNode, $constructorMethod->getNode());
    }

    private function createParameter(string $propertyType, string $propertyName): Param
    {
        return $this->builderFactory->param($propertyName)
            ->setTypeHint($propertyType);
    }

    /**
     * @return Node[]
     */
    private function createPropertyAssignment(string $propertyName): array
    {
        return $this->parser->parse(sprintf(
            '<?php $this->%s = $%s;',
            $propertyName,
            $propertyName
        ));
    }
}
