<?php

declare(strict_types=1);

namespace Rector\Generics\ValueObject;

use PHPStan\Reflection\ClassReflection;

final class GenericChildParentClassReflections
{
    /**
     * @var ClassReflection
     */
    private $childClassReflection;

    /**
     * @var ClassReflection
     */
    private $parentClassReflection;

    public function __construct(ClassReflection $childClassReflection, ClassReflection $parentClassReflection)
    {
        $this->childClassReflection = $childClassReflection;
        $this->parentClassReflection = $parentClassReflection;
    }

    public function getChildClassReflection(): ClassReflection
    {
        return $this->childClassReflection;
    }

    public function getParentClassReflection(): ClassReflection
    {
        return $this->parentClassReflection;
    }
}
