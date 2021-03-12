<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector;

use Iterator;
use Rector\Doctrine\Rector\Class_\AddUuidMirrorForRelationPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddUuidMirrorForRelationPropertyRectorTest extends AbstractRectorTestCase
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
        return AddUuidMirrorForRelationPropertyRector::class;
    }
}
