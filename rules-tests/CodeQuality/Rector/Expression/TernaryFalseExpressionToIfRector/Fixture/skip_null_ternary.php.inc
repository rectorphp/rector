<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector\Fixture;

final class SkipNullTernary
{
    public function run($value, $someMethod)
    {
        $value ?: $someMethod->call($value);
    }
}
