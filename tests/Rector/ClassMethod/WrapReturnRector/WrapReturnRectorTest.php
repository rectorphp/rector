<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\WrapReturnRector;

use Iterator;
use Rector\Rector\ClassMethod\WrapReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class WrapReturnRectorTest extends AbstractRectorTestCase
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
