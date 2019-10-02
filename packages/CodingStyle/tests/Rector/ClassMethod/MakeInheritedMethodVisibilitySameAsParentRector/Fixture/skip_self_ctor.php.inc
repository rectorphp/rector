<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

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
