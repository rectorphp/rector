<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\ValueObject\MethodName;
final class YamlToAttributeTransformer
{
    /**
     * @var ClassAttributeTransformerInterface[]
     * @readonly
     */
    private $classAttributeTransformers;
    /**
     * @var PropertyAttributeTransformerInterface[]
     * @readonly
     */
    private $propertyAttributeTransformers;
    /**
     * @param ClassAttributeTransformerInterface[] $classAttributeTransformers
     * @param PropertyAttributeTransformerInterface[] $propertyAttributeTransformers
     */
    public function __construct(iterable $classAttributeTransformers, iterable $propertyAttributeTransformers)
    {
        $this->classAttributeTransformers = $classAttributeTransformers;
        $this->propertyAttributeTransformers = $propertyAttributeTransformers;
    }
    public function transform(Class_ $class, EntityMapping $entityMapping) : void
    {
        $this->transformClass($class, $entityMapping);
        $this->transformProperties($class, $entityMapping);
    }
    private function transformClass(Class_ $class, EntityMapping $entityMapping) : void
    {
        foreach ($this->classAttributeTransformers as $classAttributeTransformer) {
            if ($this->hasAttribute($class, $classAttributeTransformer->getClassName())) {
                continue;
            }
            $classAttributeTransformer->transform($entityMapping, $class);
        }
    }
    private function transformProperties(Class_ $class, EntityMapping $entityMapping) : void
    {
        foreach ($class->getProperties() as $property) {
            foreach ($this->propertyAttributeTransformers as $propertyAttributeTransformer) {
                if ($this->hasAttribute($property, $propertyAttributeTransformer->getClassName())) {
                    continue;
                }
                $propertyAttributeTransformer->transform($entityMapping, $property);
            }
        }
        // handle promoted properties
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return;
        }
        foreach ($constructorClassMethod->getParams() as $param) {
            // is promoted property?
            if ($param->flags === 0) {
                continue;
            }
            foreach ($this->propertyAttributeTransformers as $propertyAttributeTransformer) {
                if ($this->hasAttribute($param, $propertyAttributeTransformer->getClassName())) {
                    continue;
                }
                $propertyAttributeTransformer->transform($entityMapping, $param);
            }
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $stmt
     */
    private function hasAttribute($stmt, string $attributeClassName) : bool
    {
        foreach ($stmt->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === $attributeClassName) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
