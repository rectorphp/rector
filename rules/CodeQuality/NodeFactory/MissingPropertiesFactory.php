<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\NodeFactory;
final class MissingPropertiesFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator)
    {
        $this->nodeFactory = $nodeFactory;
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
            $property = $this->nodeFactory->createPublicProperty($propertyName);
            $this->propertyTypeDecorator->decorateProperty($property, $propertyType);
            $newProperties[] = $property;
        }
        return $newProperties;
    }
}
