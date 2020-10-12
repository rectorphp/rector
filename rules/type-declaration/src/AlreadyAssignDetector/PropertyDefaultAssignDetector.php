<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\AlreadyAssignDetector;

use PhpParser\Node\Stmt\ClassLike;

final class PropertyDefaultAssignDetector
{
    public function detect(ClassLike $classLike, string $propertyName): bool
    {
        $property = $classLike->getProperty($propertyName);
        if ($property === null) {
            return false;
        }

        return $property->props[0]->default !== null;
    }
}
