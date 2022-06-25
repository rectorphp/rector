<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class SkipVoidReturn
{
    public function run()
    {
        return;
    }
}
