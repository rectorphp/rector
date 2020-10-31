<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector;

use Iterator;
use Rector\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RestoreFullyQualifiedNameRectorTest extends AbstractRectorTestCase
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
        return RestoreFullyQualifiedNameRector::class;
    }
}
