<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector;

use Iterator;
use Rector\Core\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture\SomeClass;
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
                'newArgumentPositionsByMethodAndClass' => [
                    SomeClass::class => [
                        'run' => [1, 0],
                    ],
                ],
            ],
        ];
    }
}
