<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector;

use Iterator;
use Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSingletonToServiceRectorTest extends AbstractRectorTestCase
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
        return ChangeSingletonToServiceRector::class;
    }
}
