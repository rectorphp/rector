<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\FunctionToNewRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\FuncCall\FunctionToNewRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToNewRectorTest extends AbstractRectorTestCase
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
            FunctionToNewRector::class => [
                FunctionToNewRector::FUNCTION_TO_NEW => [
                    'collection' => ['Collection'],
                ],
            ],
        ];
    }
}
