<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\FrameworkExtraBundle\TemplateAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateAnnotationRectorTest extends AbstractRectorTestCase
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
        return TemplateAnnotationRector::class;
    }
}
