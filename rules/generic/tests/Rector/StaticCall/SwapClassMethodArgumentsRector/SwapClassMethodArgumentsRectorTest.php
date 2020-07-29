<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture\SomeClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SwapClassMethodArgumentsRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            SwapClassMethodArgumentsRector::class => [
                SwapClassMethodArgumentsRector::NEW_ARGUMENT_POSITIONS_BY_METHOD_AND_CLASS => [
                    SomeClass::class => [
                        'run' => [1, 0],
                    ],
                ],
            ],
        ];
    }
}
