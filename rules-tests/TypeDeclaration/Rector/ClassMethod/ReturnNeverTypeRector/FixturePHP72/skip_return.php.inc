<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class SkipReturn
{
    public function run()
    {
        return;
    }
}
