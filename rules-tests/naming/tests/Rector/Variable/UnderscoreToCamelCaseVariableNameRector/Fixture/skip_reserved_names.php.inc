<?php

namespace Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class SkipReservedNames
{
    public function run($this__)
    {
        return $this__;
    }
}
