<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Source\ClassWithConstant;

final class SkipConstantFetch
{
    public function run()
    {
        $this->setValue(ClassWithConstant::SOME_NAME);
    }

    public function setValue(string $value)
    {
    }
}
