<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Use_\RestoreFullyQualifiedNameRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Restoration\Rector\Use_\RestoreFullyQualifiedNameRector;
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
