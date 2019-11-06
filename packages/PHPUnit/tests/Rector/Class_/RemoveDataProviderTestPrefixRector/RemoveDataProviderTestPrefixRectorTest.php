<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDataProviderTestPrefixRectorTest extends AbstractRectorTestCase
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
        return RemoveDataProviderTestPrefixRector::class;
    }
}
