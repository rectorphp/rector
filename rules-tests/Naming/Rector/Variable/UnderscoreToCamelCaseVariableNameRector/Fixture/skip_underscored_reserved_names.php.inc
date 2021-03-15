<?php

namespace Rector\Tests\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\Fixture;

final class SkipUnderscoredReservedNames
{
    public function run()
    {
        isset($_SESSION);
        return $_SERVER['host'];
    }
}
