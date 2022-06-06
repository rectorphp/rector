<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Guard;

use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Naming\Contract\Guard\ConflictingNameGuardInterface;
use RectorPrefix20220606\Rector\Naming\Contract\RenameValueObjectInterface;
use RectorPrefix20220606\Rector\Naming\ValueObject\PropertyRename;
/**
 * @implements ConflictingNameGuardInterface<PropertyRename>
 */
final class HasMagicGetSetGuard implements ConflictingNameGuardInterface
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
    /**
     * @param PropertyRename $renameValueObject
     */
    public function isConflicting(RenameValueObjectInterface $renameValueObject) : bool
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
