<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AutoImportedAnnotationToAttributeRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImported');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/auto_import.php';
    }
}
