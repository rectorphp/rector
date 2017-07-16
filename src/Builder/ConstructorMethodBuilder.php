<?php declare(strict_types=1);

namespace Rector\Builder;

use Nette\Utils\Arrays;
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

    public function __construct(Parser $parser, BuilderFactory $builderFactory)
    {
        $this->parser = $parser;
        $this->builderFactory = $builderFactory;
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

        $this->addAsFirstMethod($classNode, $constructorMethod);
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

    private function addAsFirstMethod(Class_ $classNode, Method $constructorMethod): void
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof ClassMethod) {
                Arrays::insertBefore(
                    $classNode->stmts,
                    $key,
                    [$constructorMethod->getNode()]
                );

                return;
            }
        }

        $classNode->stmts[] = $constructorMethod->getNode();
    }
}
