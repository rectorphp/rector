<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class SkipVariableName
{
    public function shorten($value)
    {
        return $value();
    }
}
