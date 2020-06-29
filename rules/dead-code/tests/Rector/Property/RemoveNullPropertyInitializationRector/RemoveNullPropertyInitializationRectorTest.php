<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveNullPropertyInitializationRectorTest extends AbstractRectorTestCase
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
        return RemoveNullPropertyInitializationRector::class;
    }
}
