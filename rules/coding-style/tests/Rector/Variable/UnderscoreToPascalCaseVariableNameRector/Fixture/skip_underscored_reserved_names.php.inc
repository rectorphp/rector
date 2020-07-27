<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableNameRector\Fixture;

final class SkipUnderscoredReservedNames
{
    public function run()
    {
        return $_SERVER['host'];
    }
}
