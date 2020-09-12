<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\Tests\Rector\MethodCall\NormalToFluentRector\Source\FluentInterfaceClass;
use Rector\Generic\ValueObject\NormalToFluent;
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
