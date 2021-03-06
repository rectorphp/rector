<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

final class SkipCallableToClosure
{
    public function run(callable $someCallback)
    {
        $this->setValue($someCallback);
    }

    public function setValue(callable $someCallaback)
    {
    }
}
