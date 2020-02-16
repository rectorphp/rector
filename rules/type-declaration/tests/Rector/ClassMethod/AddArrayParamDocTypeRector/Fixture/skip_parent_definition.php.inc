<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\SomeInterface;

final class SkipParentDefinition implements SomeInterface
{
    public function someMethod(): array
    {
        return ['test', 'test2'];
    }
}
