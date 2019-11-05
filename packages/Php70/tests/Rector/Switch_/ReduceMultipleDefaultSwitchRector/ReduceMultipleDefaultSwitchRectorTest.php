<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector;

use Iterator;
use Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReduceMultipleDefaultSwitchRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ReduceMultipleDefaultSwitchRector::class;
    }
}
