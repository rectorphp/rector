<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp81;

class DoNotCrashOnNumericString
{
    public function run(): string
    {
        return bcadd('10', '5');
    }
}