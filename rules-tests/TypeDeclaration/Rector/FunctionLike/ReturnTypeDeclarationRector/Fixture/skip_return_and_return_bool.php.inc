<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipReturnAndReturnBool
{
    public function someMethod($value)
    {
        if ($value) {
            return;
        }

        return true;
    }
}
