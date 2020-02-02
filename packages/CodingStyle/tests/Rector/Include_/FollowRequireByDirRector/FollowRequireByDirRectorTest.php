<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector;

use Iterator;
use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FollowRequireByDirRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return FollowRequireByDirRector::class;
    }
}
