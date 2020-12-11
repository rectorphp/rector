<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\Unset_\UnsetCastRector;

use Iterator;
use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP <= 8.0
 */
final class UnsetCastRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UnsetCastRector::class;
    }
}
