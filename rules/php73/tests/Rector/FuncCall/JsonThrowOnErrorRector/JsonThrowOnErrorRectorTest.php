<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class JsonThrowOnErrorRectorTest extends AbstractRectorTestCase
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
        return JsonThrowOnErrorRector::class;
    }
}
