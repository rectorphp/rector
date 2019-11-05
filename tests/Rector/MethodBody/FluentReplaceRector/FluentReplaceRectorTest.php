<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\FluentReplaceRector;

use Iterator;
use Rector\Rector\MethodBody\FluentReplaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

final class FluentReplaceRectorTest extends AbstractRectorTestCase
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
            FluentReplaceRector::class => [
                '$classesToDefluent' => [FluentInterfaceClass::class, '*Command'],
            ],
        ];
    }
}
