<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\FixtureOpenSource;

abstract class SkipAbstractClass
{
    public function run()
    {
    }
}

final class SomeChild extends SkipAbstractClass
{
}
