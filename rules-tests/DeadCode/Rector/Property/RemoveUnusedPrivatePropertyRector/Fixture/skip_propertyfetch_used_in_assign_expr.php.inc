<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

abstract class SkipPropertyFetchUsedInAssignExpr
{
    private string $foo;
    private string $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    protected function calculateMac(): string
    {
        $foobar = $this->foo;
        $foobar .= $this->bar;

        return md5($foobar);
    }
}
