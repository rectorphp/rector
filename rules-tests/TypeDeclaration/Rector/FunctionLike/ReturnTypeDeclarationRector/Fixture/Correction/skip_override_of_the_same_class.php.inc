<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class SkipOverrideOfTheSameClass
{
    public function getReturnedClass(): ReturnedClass
    {
        return new ReturnedClass();
    }
}
