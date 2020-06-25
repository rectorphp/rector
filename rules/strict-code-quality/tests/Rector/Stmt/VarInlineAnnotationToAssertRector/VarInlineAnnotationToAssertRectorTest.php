<?php

declare(strict_types=1);

namespace Rector\StrictCodeQuality\Tests\Rector\Stmt\VarInlineAnnotationToAssertRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\StrictCodeQuality\Rector\Stmt\VarInlineAnnotationToAssertRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VarInlineAnnotationToAssertRectorTest extends AbstractRectorTestCase
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
        return VarInlineAnnotationToAssertRector::class;
    }
}
