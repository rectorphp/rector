<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector;

use Iterator;
use Rector\Generic\Rector\FuncCall\RemoveIniGetSetFuncCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveIniGetSetFuncCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveIniGetSetFuncCallRector::class => [
                RemoveIniGetSetFuncCallRector::KEYS_TO_REMOVE => [
                    'y2k_compliance',
                    'safe_mode',
                    'magic_quotes_runtime',
                ],
            ],
        ];
    }
}
