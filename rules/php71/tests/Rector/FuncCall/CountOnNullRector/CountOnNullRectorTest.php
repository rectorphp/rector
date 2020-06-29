<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CountOnNullRectorTest extends AbstractRectorTestCase
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
        return CountOnNullRector::class;
    }

    protected function getPhpVersion(): string
    {
        return '7.1';
    }
}
