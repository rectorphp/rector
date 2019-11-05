<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector;

use Iterator;
use Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertFalseStrposToContainsRectorTest extends AbstractRectorTestCase
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
        return AssertFalseStrposToContainsRector::class;
    }
}
