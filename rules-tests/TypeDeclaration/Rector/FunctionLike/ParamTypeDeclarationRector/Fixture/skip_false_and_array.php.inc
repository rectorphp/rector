<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipFalseAndArray
{
    public function getMoreWhatever()
    {
        if (true) {
            return false;
        }

        return [];
    }
}
