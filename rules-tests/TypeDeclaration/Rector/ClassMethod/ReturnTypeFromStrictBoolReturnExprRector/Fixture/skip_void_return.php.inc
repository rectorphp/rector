<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class SkipVoidReturn
{
    public function run()
    {
        return;
    }
}
