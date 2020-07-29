<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\MethodBody\NormalToFluentRector;
use Rector\Generic\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NormalToFluentRectorTest extends AbstractRectorTestCase
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
            NormalToFluentRector::class => [
                NormalToFluentRector::FLUENT_METHODS_BY_TYPE => [
                    FluentInterfaceClass::class => ['someFunction', 'otherFunction', 'joinThisAsWell'],
                ],
            ],
        ];
    }
}
