<?php

declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector;

use Iterator;
use Rector\PHPStan\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveNonExistingVarAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveNonExistingVarAnnotationRector::class;
    }
}
