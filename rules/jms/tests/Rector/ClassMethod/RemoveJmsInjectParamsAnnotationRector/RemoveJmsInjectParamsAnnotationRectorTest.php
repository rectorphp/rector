<?php

declare(strict_types=1);

namespace Rector\JMS\Tests\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector;

use Iterator;
use Rector\JMS\Rector\ClassMethod\RemoveJmsInjectParamsAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveJmsInjectParamsAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveJmsInjectParamsAnnotationRector::class;
    }
}
