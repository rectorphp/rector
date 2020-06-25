<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassMethod\WrapReturnRector;

use Iterator;
use Rector\Core\Rector\ClassMethod\WrapReturnRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WrapReturnRectorTest extends AbstractRectorTestCase
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
            WrapReturnRector::class => [
                '$typeToMethodToWrap' => [
                    SomeReturnClass::class => [
                        'getItem' => 'array',
                    ],
                ],
            ],
        ];
    }
}
