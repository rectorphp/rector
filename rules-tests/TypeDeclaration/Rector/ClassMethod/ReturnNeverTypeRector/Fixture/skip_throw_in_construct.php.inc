<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class SkipThrowInConstruct
{
    public function __construct()
    {
        throw new Exception();
    }
}
