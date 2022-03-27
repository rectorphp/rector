<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipEmptyStringKey
{
    public function run(string $name)
    {
        return ['' => $name];
    }
}
