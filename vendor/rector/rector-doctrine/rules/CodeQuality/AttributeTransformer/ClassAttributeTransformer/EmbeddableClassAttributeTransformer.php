<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
final class EmbeddableClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    public function transform(EntityMapping $entityMapping, Class_ $class) : bool
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'embeddable') {
            return \false;
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName());
        return \true;
    }
    public function getClassName() : string
    {
        return MappingClass::EMBEDDABLE;
    }
}
