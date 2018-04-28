<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class MyTestCsae extends TestCase
{
    public function provideData(): \Iterator
    {
        # some comment
        yield ['item'];
    }
}
