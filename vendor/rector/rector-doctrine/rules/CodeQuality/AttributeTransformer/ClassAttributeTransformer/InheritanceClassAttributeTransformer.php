<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class InheritanceClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function transform(EntityMapping $entityMapping, Class_ $class) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $inheritanceType = $classMapping['inheritanceType'] ?? null;
        if ($inheritanceType === null) {
            return;
        }
        $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::INHERITANCE_TYPE, [$inheritanceType]);
        if (isset($classMapping['discriminatorColumn'])) {
            $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::DISCRIMINATOR_COLUMN, $classMapping['discriminatorColumn']);
        }
        if (isset($classMapping['discriminatorMap'])) {
            $this->addDiscriminatorMap($classMapping['discriminatorMap'], $class);
        }
    }
    public function getClassName() : string
    {
        return MappingClass::DISCRIMINATOR_MAP;
    }
    /**
     * @param array<string, mixed> $discriminatorMap
     */
    private function addDiscriminatorMap(array $discriminatorMap, Class_ $class) : void
    {
        $args = $this->nodeFactory->createArgs([$discriminatorMap]);
        foreach ($args as $arg) {
            if ($arg->value instanceof Array_) {
                $array = $arg->value;
                foreach ($array->items as $arrayItem) {
                    if (!$arrayItem instanceof ArrayItem) {
                        continue;
                    }
                    if (!$arrayItem->value instanceof String_) {
                        continue;
                    }
                    $string = $arrayItem->value;
                    $arrayItem->value = new ClassConstFetch(new FullyQualified($string->value), new Identifier('class'));
                }
            }
        }
        // @todo all value should be class const
        $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::DISCRIMINATOR_MAP, $args);
    }
}
