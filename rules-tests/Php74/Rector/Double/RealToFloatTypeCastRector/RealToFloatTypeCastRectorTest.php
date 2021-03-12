<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Double\RealToFloatTypeCastRector;

use Iterator;
use Rector\Php74\Rector\Double\RealToFloatTypeCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @requires PHP < 8.0
 */
final class RealToFloatTypeCastRectorTest extends AbstractRectorTestCase
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
        return RealToFloatTypeCastRector::class;
    }
}
