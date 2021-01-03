<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

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
