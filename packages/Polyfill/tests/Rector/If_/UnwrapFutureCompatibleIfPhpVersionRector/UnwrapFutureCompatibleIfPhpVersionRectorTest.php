<?php

declare(strict_types=1);

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;

use Iterator;
use Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnwrapFutureCompatibleIfPhpVersionRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UnwrapFutureCompatibleIfPhpVersionRector::class;
    }
}
