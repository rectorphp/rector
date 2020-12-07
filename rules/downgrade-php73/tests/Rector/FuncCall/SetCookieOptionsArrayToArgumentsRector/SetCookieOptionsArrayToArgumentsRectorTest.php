<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;

use Iterator;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetCookieOptionsArrayToArgumentsRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP 7.3
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
        return SetCookieOptionsArrayToArgumentsRector::class;
    }
}
