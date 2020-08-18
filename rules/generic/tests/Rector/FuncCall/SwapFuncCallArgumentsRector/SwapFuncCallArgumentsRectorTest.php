<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\SwapFuncCallArgumentsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SwapFuncCallArgumentsRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SwapFuncCallArgumentsRector::class => [
                SwapFuncCallArgumentsRector::NEW_ARGUMENT_POSITIONS_BY_FUNCTION_NAME => [
                    'some_function' => [1, 0],
                ],
            ],
        ];
    }
}
