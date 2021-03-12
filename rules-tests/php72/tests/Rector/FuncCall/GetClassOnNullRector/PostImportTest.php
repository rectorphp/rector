<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PostImportTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePostImport');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/auto_import.php';
    }
}
