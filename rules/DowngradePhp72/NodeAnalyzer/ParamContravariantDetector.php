<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ClassReflection;

final class ParamContravariantDetector
{
    /**
     * @param ClassReflection[] $ancestors
     */
    public function hasParentMethod(ClassReflection $classReflection, array $ancestors, string $classMethodName): bool
    {
        foreach ($ancestors as $ancestor) {
            if ($classReflection === $ancestor) {
                continue;
            }
            $ancestorHasMethod = $ancestor->hasMethod($classMethodName);

            if ($ancestorHasMethod) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassLike[] $classLikes
     */
    public function hasChildMethod(array $classLikes, string $classMethodName): bool
    {
        foreach ($classLikes as $classLike) {
            $currentClassMethod = $classLike->getMethod($classMethodName);
            if ($currentClassMethod !== null) {
                return true;
            }
        }

        return false;
    }
}
