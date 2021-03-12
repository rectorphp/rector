<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

use function Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Source\getExternalBool;

final class SkipExternalFunctionCaller
{
    public function getData()
    {
        return getExternalBool();
    }
}
