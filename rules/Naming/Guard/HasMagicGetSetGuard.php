<?php

declare (strict_types=1);
namespace Rector\Naming\Guard;

use PHPStan\Reflection\ReflectionProvider;
use Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use Rector\Naming\Contract\RenameValueObjectInterface;
use Rector\Naming\ValueObject\PropertyRename;
final class HasMagicGetSetGuard implements \Rector\Naming\Contract\Guard\ConflictingNameGuardInterface
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param \Rector\Naming\Contract\RenameValueObjectInterface $renameValueObject
     */
    public function isConflicting($renameValueObject) : bool
    {
        if (!$this->reflectionProvider->hasClass($renameValueObject->getClassLikeName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($renameValueObject->getClassLikeName());
        if ($classReflection->hasMethod('__set')) {
            return \true;
        }
        return $classReflection->hasMethod('__get');
    }
}
