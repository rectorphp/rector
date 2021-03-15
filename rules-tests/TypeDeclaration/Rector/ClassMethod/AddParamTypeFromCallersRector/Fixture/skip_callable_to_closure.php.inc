<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

final class SkipCallableToClosure
{
    public function run()
    {
        $someCallback = function () {
            return 1;
        };

        $this->setValue($someCallback);
    }

    public function setValue(callable $someCallaback)
    {
    }
}
