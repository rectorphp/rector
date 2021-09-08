<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

class ParentClass
{
    public function get(): ParentClass
    {
        return $this;
    }
}

class ChildClass extends ParentClass
{
    public function get(): ParentClass
    {
        return $this;
    }
}
