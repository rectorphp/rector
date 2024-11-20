<?php

declare (strict_types=1);
namespace Rector\Php74\Guard;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
final class MakePropertyTypedGuard
{
    /**
     * @readonly
     */
    private \Rector\Php74\Guard\PropertyTypeChangeGuard $propertyTypeChangeGuard;
    public function __construct(\Rector\Php74\Guard\PropertyTypeChangeGuard $propertyTypeChangeGuard)
    {
        $this->propertyTypeChangeGuard = $propertyTypeChangeGuard;
    }
    public function isLegal(Property $property, ClassReflection $classReflection, bool $inlinePublic = \true) : bool
    {
        if ($property->type instanceof Node) {
            return \false;
        }
        return $this->propertyTypeChangeGuard->isLegal($property, $classReflection, $inlinePublic);
    }
}
