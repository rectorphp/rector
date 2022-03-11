<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

class SkipNonFinalProtectedMethod
{
    protected function getValues()
    {
    }
}
