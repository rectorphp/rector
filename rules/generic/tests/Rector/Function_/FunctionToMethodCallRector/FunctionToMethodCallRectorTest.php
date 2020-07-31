<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Function_\FunctionToMethodCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Function_\FunctionToMethodCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToMethodCallRectorTest extends AbstractRectorTestCase
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
            FunctionToMethodCallRector::class => [
                FunctionToMethodCallRector::FUNCTION_TO_METHOD_CALL => [
                    'view' => ['this', 'render'],
                ],
            ],
        ];
    }
}
