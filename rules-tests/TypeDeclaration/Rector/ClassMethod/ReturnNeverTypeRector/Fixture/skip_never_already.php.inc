<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class SkipNeverAlready
{
    public function run(): never
    {
        throw new InvalidException();
    }
}
