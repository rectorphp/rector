<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\FixtureOpenSource;

abstract class SkipAbstractClass
{
    public function run()
    {
    }
}

final class SomeChild extends SkipAbstractClass
{
}
