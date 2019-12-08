<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector;

use Iterator;
use Rector\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture\SomeClass;

final class SwapClassMethodArgumentsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
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
