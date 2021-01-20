<?php

declare(strict_types=1);

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Performance\Rector\FuncCall\PreslashSimpleFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PreslashSimpleFunctionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $filename = sprintf('%s.php.inc', Option::AUTO_IMPORT_NAMES);

        if ($fileInfo->endsWith($filename)) {
            $this->setParameter(Option::AUTO_IMPORT_NAMES, true);
        }

        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return PreslashSimpleFunctionRector::class;
    }
}
