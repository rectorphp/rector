<?php

declare(strict_types=1);

namespace Rector\Privatization\Reflection;

use PHPStan\Reflection\ReflectionProvider;
use ReflectionClassConstant;

final class ParentConstantReflectionResolver
{
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function resolve(string $class, string $constant): ?ReflectionClassConstant
    {
        if (! $this->reflectionProvider->hasClass($class)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($class);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (! $parentClassReflection->hasConstant($constant)) {
                continue;
            }

            $nativeClassReflection = $parentClassReflection->getNativeReflection();

            $constantReflection = $nativeClassReflection->getConstant($constant);
            if ($constantReflection instanceof ReflectionClassConstant) {
                return $constantReflection;
            }
        }

        return null;
    }
}
