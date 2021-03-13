<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipConstructor
{
    public function __construct()
    {
        return ['123'];
    }
}
