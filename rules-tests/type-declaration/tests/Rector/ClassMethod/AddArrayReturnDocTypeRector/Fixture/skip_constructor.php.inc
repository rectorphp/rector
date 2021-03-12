<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipConstructor
{
    public function __construct()
    {
        return ['123'];
    }
}
