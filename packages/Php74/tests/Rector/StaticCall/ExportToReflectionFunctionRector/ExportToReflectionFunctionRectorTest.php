<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\StaticCall\ExportToReflectionFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;

final class ExportToReflectionFunctionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return ExportToReflectionFunctionRector::class;
    }
}
