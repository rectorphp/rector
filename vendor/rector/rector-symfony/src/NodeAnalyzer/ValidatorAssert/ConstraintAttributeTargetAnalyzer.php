<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\ValidatorAssert;

use Attribute;
use PHPStan\Reflection\ReflectionProvider;
/**
 * Symfony constraints declare the exact places they can be used in, e.g. Assert\NotBlank allows properties and
 * methods, but not classes. Writing an attribute to a place the constraint does not target would crash the
 * validator on load, so each placement is checked against the flags first.
 *
 * @see https://symfony.com/doc/current/validation.html#the-basics-of-validation
 */
final class ConstraintAttributeTargetAnalyzer
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function supportsClassTarget(string $constraintClass): bool
    {
        return $this->hasTargetFlag($constraintClass, Attribute::TARGET_CLASS);
    }
    public function supportsPropertyTarget(string $constraintClass): bool
    {
        return $this->hasTargetFlag($constraintClass, Attribute::TARGET_PROPERTY);
    }
    public function supportsMethodTarget(string $constraintClass): bool
    {
        return $this->hasTargetFlag($constraintClass, Attribute::TARGET_METHOD);
    }
    private function hasTargetFlag(string $constraintClass, int $targetFlag): bool
    {
        if (!$this->reflectionProvider->hasClass($constraintClass)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($constraintClass);
        // constraints added before Symfony 5.2 have no #[Attribute] and would crash the validator on load
        if (!$classReflection->isAttributeClass()) {
            return \false;
        }
        return ($classReflection->getAttributeClassFlags() & $targetFlag) !== 0;
    }
}
