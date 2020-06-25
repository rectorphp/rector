<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PregFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return PregFunctionToNetteUtilsStringsRector::class;
    }
}
