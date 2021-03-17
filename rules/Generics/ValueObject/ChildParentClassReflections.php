<?php

declare(strict_types=1);

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

    /**
     * Child class has priority with template map
     */
    public function getTemplateTypeMap(): TemplateTypeMap
    {
        $parentClassTemplateTypeMap = $this->parentClassReflection->getTemplateTypeMap();
        $childClassTemplateTypeMap = $this->childClassReflection->getTemplateTypeMap();

        return $childClassTemplateTypeMap->intersect($parentClassTemplateTypeMap);
    }
}
