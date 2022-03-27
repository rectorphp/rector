<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipKeyHasAt
{
    public function run(string $name)
    {
        return ['some@key' => $name];
    }
}
