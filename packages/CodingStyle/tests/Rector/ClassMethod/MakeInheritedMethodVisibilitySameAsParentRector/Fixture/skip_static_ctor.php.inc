<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

class SkipStaticCtor extends ParentWithPublicConstructor
{
    protected function __construct()
    {
    }

    public static function create()
    {
        return new self();
    }
}

class ParentWithPublicConstructor
{
    public function __construct()
    {
    }
}
