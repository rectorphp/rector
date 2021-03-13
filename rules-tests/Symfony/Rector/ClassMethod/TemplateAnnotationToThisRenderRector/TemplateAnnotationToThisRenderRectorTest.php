<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;

use Iterator;
use Rector\Symfony\Rector\ClassMethod\TemplateAnnotationToThisRenderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateAnnotationToThisRenderRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return TemplateAnnotationToThisRenderRector::class;
    }
}
