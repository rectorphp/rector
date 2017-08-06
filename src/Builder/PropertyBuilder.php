<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;

final class PropertyBuilder
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    public function __construct(BuilderFactory $builderFactory, StatementGlue $statementGlue)
    {
        $this->builderFactory = $builderFactory;
        $this->statementGlue = $statementGlue;
    }

    public function addPropertyToClass(Class_ $classNode, string $propertyType, string $propertyName): void
    {
        $propertyNode = $this->buildPrivatePropertyNode($propertyType, $propertyName);

        $this->statementGlue->addAsFirstMethod($classNode, $propertyNode);
    }

    private function buildPrivatePropertyNode(string $propertyType, string $propertyName): Property
    {
        $docComment = $this->createDocWithVarAnnotation($propertyType);

        $propertyBuilder = $this->builderFactory->property($propertyName)
            ->makePrivate()
            ->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    private function createDocWithVarAnnotation(string $propertyType): Doc
    {
        return new Doc('/**'
            . PHP_EOL . ' * @var ' . $propertyType
            . PHP_EOL . ' */');
    }
}
