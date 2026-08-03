<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\ValidatorAssert;

use PHPStan\Reflection\ReflectionProvider;
use RectorPrefix202608\Symfony\Component\Validator\Constraint;
/**
 * A constraint that adds no constructor of its own inherits the one from Symfony\Component\Validator\Constraint,
 * which takes the options array as a whole. Its options are plain properties, so there are no named arguments to
 * move them to.
 */
final class ConstraintConstructorAnalyzer
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function hasOwnConstructor(string $constraintClass): bool
    {
        if (!$this->reflectionProvider->hasClass($constraintClass)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($constraintClass);
        if (!$classReflection->hasConstructor()) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getConstructor();
        return $extendedMethodReflection->getDeclaringClass()->getName() !== Constraint::class;
    }
}
