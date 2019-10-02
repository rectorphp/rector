<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

final class SkipSelfCtor extends ParentWithPublicConstructor
{
    private function __construct()
    {
        // do something
    }

    public static function create()
    {
        return new self();
    }
}

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
        // do something more
    }
}

class ParentWithPublicConstructor
{
    public function __construct()
    {
    }
}
