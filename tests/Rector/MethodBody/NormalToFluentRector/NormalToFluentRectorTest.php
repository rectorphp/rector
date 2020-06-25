<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector;

use Iterator;
use Rector\Core\Rector\MethodBody\NormalToFluentRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NormalToFluentRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
            NormalToFluentRector::class => [
                '$fluentMethodsByType' => [
                    FluentInterfaceClass::class => ['someFunction', 'otherFunction', 'joinThisAsWell'],
                ],
            ],
        ];
    }
}
