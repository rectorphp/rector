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
    public function transform(EntityMapping $entityMapping, Class_ $class) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'embeddable') {
            return;
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName());
    }
    public function getClassName() : string
    {
        return MappingClass::EMBEDDABLE;
    }
}
