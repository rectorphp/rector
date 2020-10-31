<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector;

use Iterator;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CountOnNullRectorWithPHP73Test extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureForPhp73');
    }

    protected function getRectorClass(): string
    {
        return CountOnNullRector::class;
    }

    protected function getPhpVersion(): string
    {
        return '7.3';
    }
}
