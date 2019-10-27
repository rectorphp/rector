<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\WithConsecutiveArgToArrayRector;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\WithConsecutiveArgToArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class WithConsecutiveArgToArrayRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/skip_already_array.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return WithConsecutiveArgToArrayRector::class;
    }
}
