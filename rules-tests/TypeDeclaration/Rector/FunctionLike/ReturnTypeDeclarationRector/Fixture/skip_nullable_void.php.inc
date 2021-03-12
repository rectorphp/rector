<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipNullableVoid
{
    public function run()
    {
        if (mt_rand(1, 100)) {
            return null;
        }
    }
}
