<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeUnionTypeToDocBlockRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Downgrade\Rector\Property\DowngradeUnionTypeToDocBlockRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP >= 8.0
 */
final class DowngradeUnionTypeToDocBlockRectorTest extends AbstractRectorTestCase
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
        return DowngradeUnionTypeToDocBlockRector::class;
    }
}
