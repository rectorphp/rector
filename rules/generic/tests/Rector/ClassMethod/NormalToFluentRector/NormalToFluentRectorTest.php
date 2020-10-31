<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\Tests\Rector\ClassMethod\NormalToFluentRector\Source\FluentInterfaceClass;
use Rector\Generic\ValueObject\NormalToFluent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NormalToFluentRector::class => [
                NormalToFluentRector::CALLS_TO_FLUENT => [
                    new NormalToFluent(FluentInterfaceClass::class, [
                        'someFunction',
                        'otherFunction',
                        'joinThisAsWell',
                    ]),
                ],
            ],
        ];
    }
}
