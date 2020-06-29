<?php

declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Assign\PHPStormVarAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPStan\Rector\Assign\PHPStormVarAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PHPStormVarAnnotationRectorTest extends AbstractRectorTestCase
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
        return PHPStormVarAnnotationRector::class;
    }
}
