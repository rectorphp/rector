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

    /**
     * @param string[] $propertyTypes
     */
    public function addPropertyToClass(Class_ $classNode, array $propertyTypes, string $propertyName): void
    {
        if ($this->doesPropertyAlreadyExist($classNode, $propertyName)) {
            return;
        }

        $propertyNode = $this->buildPrivatePropertyNode($propertyTypes, $propertyName);

        $this->statementGlue->addAsFirstMethod($classNode, $propertyNode);
    }

    /**
     * @param string[] $propertyTypes
     */
    private function buildPrivatePropertyNode(array $propertyTypes, string $propertyName): Property
    {
        $docComment = $this->createDocWithVarAnnotation($propertyTypes);

        $propertyBuilder = $this->builderFactory->property($propertyName)
            ->makePrivate()
            ->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    /**
     * @param string[] $propertyTypes
     */
    private function createDocWithVarAnnotation(array $propertyTypes): Doc
    {
        return new Doc('/**'
            . PHP_EOL . ' * @var ' . implode('|', $propertyTypes)
            . PHP_EOL . ' */');
    }

    private function doesPropertyAlreadyExist(Class_ $classNode, string $propertyName): bool
    {
        foreach ($classNode->stmts as $inClassNode) {
            if (! $inClassNode instanceof Property) {
                continue;
            }

            $classPropertyName = (string) $inClassNode->props[0]->name;

            if ($classPropertyName === $propertyName) {
                return true;
            }
        }

        return false;
    }
}
