<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipInnerFunctionReturn
{
    public function go()
    {
        $callables = function () {
            return [1];
        };
    }
}
