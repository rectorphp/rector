<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Source\vendor;

trait Foo
{
    public function load($resource, string $type = null): string
    {
    }
}
