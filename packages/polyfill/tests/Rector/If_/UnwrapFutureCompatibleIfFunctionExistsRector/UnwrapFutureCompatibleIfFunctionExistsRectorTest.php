<?php

declare(strict_types=1);

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Polyfill\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;

final class UnwrapFutureCompatibleIfFunctionExistsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UnwrapFutureCompatibleIfFunctionExistsRector::class;
    }
}
