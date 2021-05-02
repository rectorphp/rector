<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class SkipReturn
{
    public function run()
    {
        return;
    }
}
