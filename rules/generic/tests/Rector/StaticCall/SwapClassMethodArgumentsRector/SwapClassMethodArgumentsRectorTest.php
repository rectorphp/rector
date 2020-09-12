<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Generic\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture\SomeClass;
use Rector\Generic\ValueObject\SwapClassMethodArguments;
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SwapClassMethodArgumentsRector::class => [
                SwapClassMethodArgumentsRector::ARGUMENT_SWAPS => [
                    new SwapClassMethodArguments(SomeClass::class, 'run', [1, 0]),
                ],
            ],
        ];
    }
}
