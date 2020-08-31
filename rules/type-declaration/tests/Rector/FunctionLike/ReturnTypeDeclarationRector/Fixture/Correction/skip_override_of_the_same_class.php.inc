<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Correction;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ReturnedClass;

class SkipOverrideOfTheSameClass
{
    public function getReturnedClass(): ReturnedClass
    {
        return new ReturnedClass();
    }
}
