<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class SkipUnderscoredReservedNames
{
    public function run()
    {
        return $_SERVER['host'];
    }
}
