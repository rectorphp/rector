<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property as PhpParserProperty;
use Rector\Builder\Class_\Property;
use Rector\Naming\PropertyNaming;

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

    public function addPropertyToClass(Class_ $classNode, Property $property): void
    {
        if ($this->doesPropertyAlreadyExist($classNode, $property)) {
            return;
        }

        $propertyNode = $this->buildPrivatePropertyNode($property);

        $this->statementGlue->addAsFirstMethod($classNode, $propertyNode);
    }

    private function buildPrivatePropertyNode(Property $property): PhpParserProperty
    {
        $docComment = $this->createDocWithVarAnnotation($property->getTypes());

        $propertyBuilder = $this->builderFactory->property($property->getName())
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
            . PHP_EOL . ' * @var ' . $this->implodeTypes($propertyTypes)
            . PHP_EOL . ' */');
    }

    private function doesPropertyAlreadyExist(Class_ $classNode, Property $property): bool
    {
        foreach ($classNode->stmts as $inClassNode) {
            if (! $inClassNode instanceof PhpParserProperty) {
                continue;
            }

            $classPropertyName = (string) $inClassNode->props[0]->name;

            if ($classPropertyName === $property->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $propertyTypes
     */
    private function implodeTypes(array $propertyTypes): string
    {
        $implodedTypes = '';
        foreach ($propertyTypes as $propertyType) {
            $implodedTypes .= PropertyNaming::isPhpReservedType($propertyType)
                ? $propertyType
                : '\\' . $propertyType . '|';
        }

        return rtrim($implodedTypes, '|');
    }
}
