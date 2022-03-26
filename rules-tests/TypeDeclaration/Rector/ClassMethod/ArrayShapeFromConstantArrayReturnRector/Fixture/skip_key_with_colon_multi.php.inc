<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipKeyWithColonMulti
{
    public function run(string $name)
    {
        return ['prefix:key' => $name, 'another:key' => $name];
    }
}
