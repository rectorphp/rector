<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AutoImportedAnnotationToAttributeRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureAutoImported');
    }

    protected function getRectorClass(): string
    {
        return AnnotationToAttributeRector::class;
    }
}
