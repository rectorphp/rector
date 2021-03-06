<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

final class SkipBooleanCast
{
    public function run()
    {
        $this->setValue((bool) 1);
    }

    public function setValue(bool $isValue)
    {
    }
}
