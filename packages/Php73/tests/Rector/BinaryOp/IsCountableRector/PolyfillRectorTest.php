<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector;

use Iterator;
use Rector\Php73\Rector\BinaryOp\IsCountableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PolyfillRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithPolyfill');
    }

    protected function getPhpVersion(): string
    {
        return '7.2';
    }

    protected function getRectorClass(): string
    {
        return IsCountableRector::class;
    }
}
