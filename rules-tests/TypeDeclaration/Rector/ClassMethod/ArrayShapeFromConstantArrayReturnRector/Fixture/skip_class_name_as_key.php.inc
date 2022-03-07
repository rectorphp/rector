<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipClassNameAsKey
{
    public function run(string $name)
    {
        return ['ArrayAccess' => $name];
    }
}
