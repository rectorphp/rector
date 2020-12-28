<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class SkipTypedParamVariadic
{
    public function run(array ...$var)
    {
    }
}
