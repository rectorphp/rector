<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipEmptyArray
{
    public function run()
    {
        return [];
    }
}
