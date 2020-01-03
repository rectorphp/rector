<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveImmediateAssignUseRector;

use Iterator;
use Rector\CodeQuality\Rector\FunctionLike\RemoveImmediateAssignUseRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveImmediateAssignUseRectorTest extends AbstractRectorTestCase
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
        return RemoveImmediateAssignUseRector::class;
    }
}
