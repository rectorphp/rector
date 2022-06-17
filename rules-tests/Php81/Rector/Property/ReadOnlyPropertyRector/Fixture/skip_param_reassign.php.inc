<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipParamReassign
{
    public function __construct(private string $foo)
    {
        $foo = 'bar';

        $this->foo = $foo;
    }
}
