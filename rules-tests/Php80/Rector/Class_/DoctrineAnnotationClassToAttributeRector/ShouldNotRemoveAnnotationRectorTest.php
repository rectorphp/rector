<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ShouldNotRemoveAnnotationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureShouldNotRemoveAnnotation');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/should_not_remove_annotation.php';
    }
}
