<?php

declare (strict_types=1);
namespace Rector\Generics\NodeType;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDoc\Tag\ExtendsTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateObjectType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeParameterStrategy;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
final class ExtendsTemplateTypeMapFallbackFactory
{
    public function createFromClassReflection(\PHPStan\Reflection\ClassReflection $classReflection) : ?\PHPStan\Type\Generic\TemplateTypeMap
    {
        $extendsTags = $this->resolveExtendsTags($classReflection);
        if ($extendsTags === []) {
            return null;
        }
        $parentGenericTypeNames = $this->resolveParentGenericTypeNames($classReflection);
        foreach ($extendsTags as $extendTag) {
            $extendsTagType = $extendTag->getType();
            if (!$extendsTagType instanceof \PHPStan\Type\Generic\GenericObjectType) {
                continue;
            }
            $templateTypeMap = [];
            foreach ($extendsTagType->getTypes() as $key => $genericExtendType) {
                if (!isset($parentGenericTypeNames[$key])) {
                    continue;
                }
                $parentGenericTypeName = $parentGenericTypeNames[$key];
                if ($genericExtendType instanceof \PHPStan\Type\TypeWithClassName) {
                    // this plac ewill need more work
                    $templateTypeScope = \PHPStan\Type\Generic\TemplateTypeScope::createWithClass($classReflection->getName());
                    $genericExtendType = $this->createTemplateObjectType($templateTypeScope, $parentGenericTypeName, $genericExtendType);
                }
                $templateTypeMap[$parentGenericTypeName] = $genericExtendType;
            }
            return new \PHPStan\Type\Generic\TemplateTypeMap($templateTypeMap);
        }
        return null;
    }
    /**
     * @return ExtendsTag[]
     */
    private function resolveExtendsTags(\PHPStan\Reflection\ClassReflection $classReflection) : array
    {
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return [];
        }
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof \PHPStan\PhpDoc\ResolvedPhpDocBlock) {
            return [];
        }
        return $resolvedPhpDocBlock->getExtendsTags();
    }
    /**
     * @return string[]
     */
    private function resolveParentGenericTypeNames(\PHPStan\Reflection\ClassReflection $classReflection) : array
    {
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return [];
        }
        $templateTypeMap = $parentClassReflection->getTemplateTypeMap();
        return \array_keys($templateTypeMap->getTypes());
    }
    private function createTemplateObjectType(\PHPStan\Type\Generic\TemplateTypeScope $templateTypeScope, string $parentGenericTypeName, \PHPStan\Type\TypeWithClassName $typeWithClassName) : \PHPStan\Type\Generic\TemplateObjectType
    {
        return new \PHPStan\Type\Generic\TemplateObjectType($templateTypeScope, new \PHPStan\Type\Generic\TemplateTypeParameterStrategy(), \PHPStan\Type\Generic\TemplateTypeVariance::createInvariant(), $parentGenericTypeName, new \PHPStan\Type\ObjectType($typeWithClassName->getClassName()));
    }
}
