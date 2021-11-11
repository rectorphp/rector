<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class SkipPropertyFetchUsedInAssignExpr2
{
    private int $foo;

    public function __construct(int $foo)
    {
        $this->foo = $foo;
    }

    public function calculateMac(): int
    {
        $foobar = $this->foo++;
        return $foobar;
    }
}
