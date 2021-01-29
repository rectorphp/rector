<?php

declare(strict_types=1);

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
use PHPStan\Type\TypeWithClassName;

final class ExtendsTemplateTypeMapFallbackFactory
{
    public function createFromClassReflection(ClassReflection $classReflection): ?TemplateTypeMap
    {
        $extendsTags = $this->resolveExtendsTags($classReflection);
        if ($extendsTags === []) {
            return null;
        }

        $parentGenericTypeNames = $this->resolveParentGenericTypeNames($classReflection);

        foreach ($extendsTags as $extendsTag) {
            $extendsTagType = $extendsTag->getType();
            if (! $extendsTagType instanceof GenericObjectType) {
                continue;
            }

            $templateTypeMap = [];
            foreach ($extendsTagType->getTypes() as $key => $genericExtendType) {
                if (! isset($parentGenericTypeNames[$key])) {
                    continue;
                }

                $parentGenericTypeName = $parentGenericTypeNames[$key];

                if ($genericExtendType instanceof TypeWithClassName) {
                    // this plac ewill need more work
                    $templateTypeScope = TemplateTypeScope::createWithClass($classReflection->getName());
                    $genericExtendType = $this->createTemplateObjectType(
                        $templateTypeScope,
                        $parentGenericTypeName,
                        $genericExtendType
                    );
                }

                $templateTypeMap[$parentGenericTypeName] = $genericExtendType;
            }

            return new TemplateTypeMap($templateTypeMap);
        }

        return null;
    }

    /**
     * @return ExtendsTag[]
     */
    private function resolveExtendsTags(ClassReflection $classReflection): array
    {
        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection instanceof ClassReflection) {
            return [];
        }

        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (! $resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return [];
        }

        return $resolvedPhpDocBlock->getExtendsTags();
    }

    /**
     * @return string[]
     */
    private function resolveParentGenericTypeNames(ClassReflection $classReflection): array
    {
        $parentClassReflection = $classReflection->getParentClass();
        if (! $parentClassReflection instanceof ClassReflection) {
            return [];
        }

        $templateTypeMap = $parentClassReflection->getTemplateTypeMap();
        return array_keys($templateTypeMap->getTypes());
    }

    private function createTemplateObjectType(
        TemplateTypeScope $templateTypeScope,
        string $parentGenericTypeName,
        TypeWithClassName $typeWithClassName
    ): TemplateObjectType {
        return new TemplateObjectType(
            $templateTypeScope,
            new TemplateTypeParameterStrategy(),
            TemplateTypeVariance::createInvariant(),
            $parentGenericTypeName,
            $typeWithClassName->getClassName()
        );
    }
}
