<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Rector\Generic\ValueObject\WrapReturn;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WrapReturnRectorTest extends AbstractRectorTestCase
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
            WrapReturnRector::class => [
                WrapReturnRector::TYPE_METHOD_WRAPS => [new WrapReturn(SomeReturnClass::class, 'getItem', true)],
            ],
        ];
    }
}
