<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\SetcookieRector;

use Iterator;
use Rector\Php73\Rector\FuncCall\SetCookieRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetCookieRectorTest extends AbstractRectorTestCase
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
        return SetCookieRector::class;
    }
}
