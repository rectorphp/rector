<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipTestMethod extends TestCase
{
    public function test(string $name, ...$items)
    {
    }
}
