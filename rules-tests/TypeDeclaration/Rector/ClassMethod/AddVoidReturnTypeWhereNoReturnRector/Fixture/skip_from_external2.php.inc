<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Source\vendor\Foo;

final class SkipFromExternal2
{
    use Foo;

    public function load($resource, string $type = null)
    {
    }
}
