<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class SkipNeverAlready
{
    public function run(): never
    {
        throw new InvalidException();
    }
}
