<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\PreslashSimpleFunctionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AutoImportTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(Option::AUTO_IMPORT_NAMES, true);
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImport');
    }

    protected function getRectorClass(): string
    {
        return PreslashSimpleFunctionRector::class;
    }
}
