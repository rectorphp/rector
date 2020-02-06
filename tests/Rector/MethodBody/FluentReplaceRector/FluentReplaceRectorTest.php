<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector;

use Iterator;
use Rector\Core\Rector\MethodBody\FluentReplaceRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector\Source\FluentInterfaceClass;

final class FluentReplaceRectorTest extends AbstractRectorTestCase
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
