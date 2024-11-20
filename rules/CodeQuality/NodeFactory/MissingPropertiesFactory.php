<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Modifiers;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
final class MissingPropertiesFactory
{
    /**
     * @readonly
     */
    private \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator;
    public function __construct(\Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
    }
    /**
     * @param array<string, Type> $fetchedLocalPropertyNameToTypes
     * @param string[] $propertyNamesToComplete
     * @return Property[]
     */
    public function create(array $fetchedLocalPropertyNameToTypes, array $propertyNamesToComplete) : array
    {
        $newProperties = [];
        foreach ($fetchedLocalPropertyNameToTypes as $propertyName => $propertyType) {
            if (!\in_array($propertyName, $propertyNamesToComplete, \true)) {
                continue;
            }
            $property = new Property(Modifiers::PUBLIC, [new PropertyItem($propertyName)]);
            $this->propertyTypeDecorator->decorateProperty($property, $propertyType);
            $newProperties[] = $property;
        }
        return $newProperties;
    }
}
