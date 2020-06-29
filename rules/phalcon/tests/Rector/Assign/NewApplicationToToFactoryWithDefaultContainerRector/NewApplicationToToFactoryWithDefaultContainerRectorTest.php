<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewApplicationToToFactoryWithDefaultContainerRectorTest extends AbstractRectorTestCase
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
        return NewApplicationToToFactoryWithDefaultContainerRector::class;
    }
}
