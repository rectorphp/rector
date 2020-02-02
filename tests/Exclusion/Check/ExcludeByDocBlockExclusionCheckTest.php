<?php

declare(strict_types=1);

namespace Rector\Tests\Exclusion\Check;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExcludeByDocBlockExclusionCheckTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveEmptyClassMethodRector::class => [],
            RemoveDeadZeroAndOneOperationRector::class => [],
        ];
    }
}
