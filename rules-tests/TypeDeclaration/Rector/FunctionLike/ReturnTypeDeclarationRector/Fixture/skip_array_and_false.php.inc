<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class SkipArrayAndFalse
{
    public function getMoreWhatever()
    {
        if (true) {
            return false;
        }

        return [];
    }
}
