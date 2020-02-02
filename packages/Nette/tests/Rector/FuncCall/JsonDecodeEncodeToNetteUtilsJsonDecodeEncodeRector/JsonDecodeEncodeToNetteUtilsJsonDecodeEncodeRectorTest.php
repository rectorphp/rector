<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;

use Iterator;
use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest extends AbstractRectorTestCase
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
        return JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class;
    }
}
