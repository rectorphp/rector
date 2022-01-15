<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class SkipThrowAsExpr
{
    public function run($someClass)
    {
        $this->foo = $someClass ?: throw new Exception('current request is null');
    }
}
