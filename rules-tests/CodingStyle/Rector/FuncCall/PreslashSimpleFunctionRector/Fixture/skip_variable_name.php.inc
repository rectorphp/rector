<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SkipVariableName
{
    public function shorten($value)
    {
        return $value();
    }
}
