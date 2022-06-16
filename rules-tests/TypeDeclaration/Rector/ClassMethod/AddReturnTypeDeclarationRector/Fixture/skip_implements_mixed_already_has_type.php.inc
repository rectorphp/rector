<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\DataTransformerInterface;

final class MyDataTransformer implements DataTransformerInterface
{
    public function transform(): string
    {
        return 'foo';
    }
}
