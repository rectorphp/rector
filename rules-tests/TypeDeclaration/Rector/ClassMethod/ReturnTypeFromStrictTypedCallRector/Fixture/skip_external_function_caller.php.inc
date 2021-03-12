<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use function Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\getExternalBool;

final class SkipExternalFunctionCaller
{
    public function getData()
    {
        return getExternalBool();
    }
}
