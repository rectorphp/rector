<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipInnerFunctionReturn
{
    public function go()
    {
        $callables = function () {
            return [1];
        };
    }
}
