<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

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
