<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SkipConstructorForVoid
{
    public function __construct()
    {
        $value = 1000;
    }
}
