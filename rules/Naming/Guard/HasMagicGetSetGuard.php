<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use PHPStan\Reflection\ReflectionProvider;
use Rector\Naming\ValueObject\PropertyRename;
final class HasMagicGetSetGuard
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isConflicting(PropertyRename $propertyRename) : bool
    {
        if (!$this->reflectionProvider->hasClass($propertyRename->getClassLikeName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($propertyRename->getClassLikeName());
        if ($classReflection->hasMethod('__set')) {
            return \true;
        }
        return $classReflection->hasMethod('__get');
    }
}
