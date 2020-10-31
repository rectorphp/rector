<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;

use Iterator;
use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class;
    }
}
