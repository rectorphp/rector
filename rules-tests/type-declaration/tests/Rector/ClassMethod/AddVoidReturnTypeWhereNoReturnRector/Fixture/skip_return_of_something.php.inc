<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SkipReturnOfSomething
{
    public function getValues()
    {
        $value = 1000;
        if ($value) {
            return;
        }

        return 10;
    }
}
