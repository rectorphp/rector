<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class ANewClass
{
    public function getObject()
    {
        return new class() {};
    }
}
