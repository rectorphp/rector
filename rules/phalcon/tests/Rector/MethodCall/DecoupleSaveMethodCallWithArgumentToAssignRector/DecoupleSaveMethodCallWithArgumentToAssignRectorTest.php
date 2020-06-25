<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Phalcon\Rector\MethodCall\DecoupleSaveMethodCallWithArgumentToAssignRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DecoupleSaveMethodCallWithArgumentToAssignRectorTest extends AbstractRectorTestCase
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
        return DecoupleSaveMethodCallWithArgumentToAssignRector::class;
    }
}
