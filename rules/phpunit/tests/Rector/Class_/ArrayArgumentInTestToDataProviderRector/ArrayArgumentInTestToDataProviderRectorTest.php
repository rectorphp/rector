<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayArgumentInTestToDataProviderRectorTest extends AbstractRectorTestCase
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
            ArrayArgumentInTestToDataProviderRector::class => [
                ArrayArgumentInTestToDataProviderRector::CONFIGURATION => [
                    [
                        'class' => 'PHPUnit\Framework\TestCase',
                        'old_method' => 'doTestMultiple',
                        'new_method' => 'doTestSingle',
                        'variable_name' => 'variable',
                    ],
                ],
            ],
        ];
    }
}
