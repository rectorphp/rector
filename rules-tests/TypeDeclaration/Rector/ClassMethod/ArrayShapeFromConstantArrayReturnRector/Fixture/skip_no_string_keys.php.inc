<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

use PhpParser\Node\Stmt\ClassMethod;

final class SkipNoStringKeys
{
    public function run()
    {
        return [ClassMethod::class];
    }
}
