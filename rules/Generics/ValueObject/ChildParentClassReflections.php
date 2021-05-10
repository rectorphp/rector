<?php

declare (strict_types=1);
namespace Rector\Generics\ValueObject;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Generic\TemplateTypeMap;
final class ChildParentClassReflections
{
    /**
     * @var ClassReflection
     */
    private $childClassReflection;
    /**
     * @var ClassReflection
     */
    private $parentClassReflection;
    public function __construct(\PHPStan\Reflection\ClassReflection $childClassReflection, \PHPStan\Reflection\ClassReflection $parentClassReflection)
    {
        $this->childClassReflection = $childClassReflection;
        $this->parentClassReflection = $parentClassReflection;
    }
    public function getChildClassReflection() : \PHPStan\Reflection\ClassReflection
    {
        return $this->childClassReflection;
    }
    public function getParentClassReflection() : \PHPStan\Reflection\ClassReflection
    {
        return $this->parentClassReflection;
    }
    /**
     * Child class has priority with template map
     */
    public function getTemplateTypeMap() : \PHPStan\Type\Generic\TemplateTypeMap
    {
        $parentClassTemplateTypeMap = $this->parentClassReflection->getTemplateTypeMap();
        $childClassTemplateTypeMap = $this->childClassReflection->getTemplateTypeMap();
        return $childClassTemplateTypeMap->intersect($parentClassTemplateTypeMap);
    }
}
