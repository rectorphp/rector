<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeFactory;

use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class RelationPropertyFactory
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(ValueResolver $valueResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->valueResolver = $valueResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
            $manyToOneTagValueNode = new ManyToOneTagValueNode([
                'targetEntity' => $className,
            ], null, $className);

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            $phpDocInfo->addTagValueNodeWithShortName($manyToOneTagValueNode);

            // add @ORM\JoinColumn
            $joinColumnTagValueNode = new JoinColumnTagValueNode([
                'name' => $manyToOneConfiguration['foreignKey'],
            ]);

            $phpDocInfo->addTagValueNodeWithShortName($joinColumnTagValueNode);

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @return Property[]
     */
    public function createOneToOneProperties(Property $hasOneProperty): array
    {
        $propertyDefaultValue = $this->getPropertyDefaultValue($hasOneProperty);

        $properties = [];
        foreach ($propertyDefaultValue as $propertyName => $relationConfiguration) {
            $property = $this->createPrivateProperty($propertyName);

            // add @ORM\OneToOne
            $oneToOneTagValueNode = new OneToOneTagValueNode([
                'targetEntity' => $relationConfiguration['className'],
            ]);

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            $phpDocInfo->addTagValueNodeWithShortName($oneToOneTagValueNode);

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

            // add @ORM\ManyToOne
            $manyToOneTagValueNode = new ManyToManyTagValueNode([
                'targetEntity' => $manyToOneConfiguration['className'],
            ]);

            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            $phpDocInfo->addTagValueNodeWithShortName($manyToOneTagValueNode);

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * @return Property[]
     */
    public function createOneToManyProperties(Property $hasManyProperty): array
    {
        $propertyDefaultValue = $this->getPropertyDefaultValue($hasManyProperty);

        $properties = [];
        foreach ($propertyDefaultValue as $propertyName => $relationConfiguration) {
            $property = $this->createPrivateProperty($propertyName);

            $className = $relationConfiguration['className'];

            // add @ORM\OneToMany
            $manyToOneTagValueNode = new OneToManyTagValueNode(['targetEntity' => $className]);

            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
            $phpDocInfo->addTagValueNodeWithShortName($manyToOneTagValueNode);

            $properties[] = $property;
        }

        return $properties;
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

    private function createPrivateProperty(string $propertyName): Property
    {
        $propertyName = lcfirst($propertyName);

        $propertyBuilder = new PropertyBuilder($propertyName);
        $propertyBuilder->makePrivate();

        return $propertyBuilder->getNode();
    }
}
