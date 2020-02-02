<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\ConstFetch\BarewordStringRector;

use Iterator;
use Rector\Php72\Rector\ConstFetch\BarewordStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BarewordStringRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return BarewordStringRector::class;
    }
}
