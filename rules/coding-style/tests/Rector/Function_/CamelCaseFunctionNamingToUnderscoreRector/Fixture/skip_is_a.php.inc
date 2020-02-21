<?php

namespace Rector\CodingStyle\Tests\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector\Fixture;

class SkipIsA
{
    public function run()
    {
        return is_a($this, self::class);
    }
}
