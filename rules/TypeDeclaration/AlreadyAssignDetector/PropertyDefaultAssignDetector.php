<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\AlreadyAssignDetector;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
final class PropertyDefaultAssignDetector
{
    public function detect(ClassLike $classLike, string $propertyName) : bool
    {
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof Property) {
            return \false;
        }
        return $property->props[0]->default !== null;
    }
}
