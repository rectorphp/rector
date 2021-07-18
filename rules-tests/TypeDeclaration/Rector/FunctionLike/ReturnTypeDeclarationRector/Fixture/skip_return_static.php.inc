<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipReturnStatic
{
    public function run()
    {
        return new static();
    }
}
