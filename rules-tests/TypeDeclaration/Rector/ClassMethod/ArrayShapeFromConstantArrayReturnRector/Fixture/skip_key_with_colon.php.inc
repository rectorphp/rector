<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipKeyWithColon
{
    public function run(string $name)
    {
        return ['prefix:key' => $name];
    }
}
