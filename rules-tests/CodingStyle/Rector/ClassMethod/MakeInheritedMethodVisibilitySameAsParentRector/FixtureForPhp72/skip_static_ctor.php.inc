<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Source\ParentWithPublicConstructor;

abstract class SkipStaticCtor extends ParentWithPublicConstructor
{
    protected function __construct()
    {
        // do something basic
    }

    public static function create()
    {
        return new static();
    }
}

class InheritedSkipCtor extends SkipStaticCtor
{
    protected function __construct()
    {
        parent::__construct();

        // do something more
    }
}
