<?php

declare(strict_types=1);

namespace Rector\Tests\Exclusion\Check;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExcludeByDocBlockExclusionCheckTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveEmptyClassMethodRector::class => [],
            RemoveZeroAndOneBinaryRector::class => [],
        ];
    }
}
