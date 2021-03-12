<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Tests\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;

use Iterator;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrowFunctionToAnonymousFunctionRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.4
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
        return ArrowFunctionToAnonymousFunctionRector::class;
    }
}
