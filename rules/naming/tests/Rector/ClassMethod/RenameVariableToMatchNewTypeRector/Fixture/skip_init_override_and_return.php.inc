<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;

final class SkipInitOverrideAndReturn
{
    public function run(string $parentClassName)
    {
        $type = new ObjectType($parentClassName);

        $parentParentClass = get_parent_class($parentClassName);
        if ($parentParentClass) {
            $type = new UnionType([$type, new ObjectType($parentParentClass)]);
        }

        return $type;
    }
}
