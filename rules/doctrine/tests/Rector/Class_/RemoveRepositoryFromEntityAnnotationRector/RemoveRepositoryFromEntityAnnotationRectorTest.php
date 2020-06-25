<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Doctrine\Rector\Class_\RemoveRepositoryFromEntityAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveRepositoryFromEntityAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveRepositoryFromEntityAnnotationRector::class;
    }
}
