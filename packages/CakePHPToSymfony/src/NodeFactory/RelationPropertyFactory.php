<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeFactory;

use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;

final class RelationPropertyFactory
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(ValueResolver $valueResolver, DocBlockManipulator $docBlockManipulator)
    {
        $this->valueResolver = $valueResolver;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return Property[]
     */
    public function createManyToOneProperties(Property $belongToProperty): array
    {
        $belongsToValue = $this->getPropertyDefaultValue($belongToProperty);

        $properties = [];
        foreach ($belongsToValue as $propertyName => $manyToOneConfiguration) {
            $property = $this->createPrivateProperty($propertyName);

            $className = $manyToOneConfiguration['className'];

            // add @ORM\ManyToOne
            $manyToOneTagValueNode = new ManyToOneTagValueNode($className, null, null, null, null, $className);
            $this->docBlockManipulator->addTagValueNodeWithShortName($property, $manyToOneTagValueNode);

            // add @ORM\JoinColumn
            $joinColumnTagValueNode = new JoinColumnTagValueNode($manyToOneConfiguration['foreignKey'], null);
            $this->docBlockManipulator->addTagValueNodeWithShortName($property, $joinColumnTagValueNode);

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @return Property[]
     */
    public function createManyToManyProperties(Property $hasAndBelongsToManyProperty): array
    {
        $hasAndBelongsToValue = $this->getPropertyDefaultValue($hasAndBelongsToManyProperty);

        $properties = [];
        foreach ($hasAndBelongsToValue as $propertyName => $manyToOneConfiguration) {
            $property = $this->createPrivateProperty($propertyName);

            $className = $manyToOneConfiguration['className'];

            // add @ORM\ManyToOne
            $manyToOneTagValueNode = new ManyToManyTagValueNode($className);
            $this->docBlockManipulator->addTagValueNodeWithShortName($property, $manyToOneTagValueNode);

            $properties[] = $property;
        }

        return $properties;
    }

    private function createPrivateProperty(string $propertyName): Property
    {
        $propertyName = lcfirst($propertyName);

        $propertyBuilder = new PropertyBuilder($propertyName);
        $propertyBuilder->makePrivate();

        return $propertyBuilder->getNode();
    }

    private function getPropertyDefaultValue(Property $property): array
    {
        if (count((array) $property->props) !== 1) {
            throw new ShouldNotHappenException();
        }

        $onlyPropertyDefault = $property->props[0]->default;
        if ($onlyPropertyDefault === null) {
            throw new ShouldNotHappenException();
        }

        $value = $this->valueResolver->getValue($onlyPropertyDefault);
        if (! is_array($value)) {
            throw new ShouldNotHappenException();
        }

        return $value;
    }
}
