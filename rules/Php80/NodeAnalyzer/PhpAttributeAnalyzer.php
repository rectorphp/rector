<?php

declare(strict_types=1);

namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class PhpAttributeAnalyzer
{
    public function __construct(
        private AstResolver $astResolver,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function hasPhpAttribute(Property | ClassLike | ClassMethod | Param $node, string $attributeClass): bool
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (! $this->nodeNameResolver->isName($attribute->name, $attributeClass)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    public function hasInheritedPhpAttribute(ClassLike $classLike, string $attributeClass): bool
    {
        $className = (string) $this->nodeNameResolver->getName($classLike);
        if (! $this->reflectionProvider->hasClass($className)) {
            return false;
        }

        $reflectionClass = $this->reflectionProvider->getClass($className);
        $ancestorClassReflections = $reflectionClass->getAncestors();
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            $ancestorClassName = $ancestorClassReflection->getName();
            if ($ancestorClassName === $className) {
                continue;
            }

            $class = $this->astResolver->resolveClassFromName($ancestorClassName);
            if (! $class instanceof Class_) {
                continue;
            }

            if ($this->hasPhpAttribute($class, $attributeClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $attributeClasses
     */
    public function hasPhpAttributes(Property | ClassLike | ClassMethod | Param $node, array $attributeClasses): bool
    {
        foreach ($attributeClasses as $attributeClass) {
            if ($this->hasPhpAttribute($node, $attributeClass)) {
                return true;
            }
        }

        return false;
    }
}
