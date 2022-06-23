<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\FormTypeInterface;

final class MyFormType implements FormTypeInterface
{
    public function getParent(): string
    {
        return 'foo';
    }
}
