<?php

namespace Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture;

use Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source;
use Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source\FirstLog;
use Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source\SecondLog;

final class UnionPartialImport
{
    public function run(): Source\FirstLog|Source\SecondLog
    {
        if (rand(0, 1)) {
            return new FirstLog();
        }

        return new SecondLog();
    }
}
