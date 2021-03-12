<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Source\ParentWithPublicConstructor;

final class SkipSelfCtor extends ParentWithPublicConstructor
{
    private function __construct()
    {
    }

    public static function create()
    {
        return new self();
    }
}
