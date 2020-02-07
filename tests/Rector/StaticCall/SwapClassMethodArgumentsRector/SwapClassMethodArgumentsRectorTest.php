<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector;

use Iterator;
use Rector\Core\Rector\StaticCall\SwapClassMethodArgumentsRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture\SomeClass;

final class SwapClassMethodArgumentsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
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
