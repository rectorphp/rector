<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Source;

trait Foo
{
    public function load($resource, string $type = null)
    {
    }
}