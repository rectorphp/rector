<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
final class IdAttributeTransformer implements PropertyAttributeTransformerInterface
{
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property) : bool
    {
        $idMapping = $entityMapping->matchIdPropertyMapping($property);
        if (!\is_array($idMapping)) {
            return \false;
        }
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName());
        return \true;
    }
    public function getClassName() : string
    {
        return MappingClass::ID;
    }
}
