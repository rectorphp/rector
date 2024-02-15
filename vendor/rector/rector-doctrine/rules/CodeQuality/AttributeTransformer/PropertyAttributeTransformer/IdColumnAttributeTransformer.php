<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
final class IdColumnAttributeTransformer implements PropertyAttributeTransformerInterface
{
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property) : void
    {
        $idMapping = $entityMapping->matchIdPropertyMapping($property);
        if (!\is_array($idMapping)) {
            return;
        }
        $args = [];
        $type = $idMapping['type'] ?? null;
        if ($type) {
            $args[] = AttributeFactory::createNamedArg($type, 'type');
        }
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
    }
    public function getClassName() : string
    {
        return MappingClass::COLUMN;
    }
}
