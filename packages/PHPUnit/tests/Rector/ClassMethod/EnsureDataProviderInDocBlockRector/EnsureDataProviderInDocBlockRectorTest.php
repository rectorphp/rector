<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector;

use Iterator;
use Rector\PHPUnit\Rector\ClassMethod\EnsureDataProviderInDocBlockRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EnsureDataProviderInDocBlockRectorTest extends AbstractRectorTestCase
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
        return EnsureDataProviderInDocBlockRector::class;
    }
}
