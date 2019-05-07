<?php

namespace Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector\Fixture;

final class KeepPropertyChangedInAnotherMethod
{
    /**
     * @var bool
     */
    private $isBoolAssert = false;

    public function createAssertMethod()
    {
        $this->isBoolAssert = false;

        $this->changeIsBoolAssert();

        return ! $this->isBoolAssert && $expected;
    }

    public function changeIsBoolAssert()
    {
        $this->isBoolAssert = true;
    }
}
