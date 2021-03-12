<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector;

use Iterator;
use Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameEventNamesInEventSubscriberRectorTest extends AbstractRectorTestCase
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
        return RenameEventNamesInEventSubscriberRector::class;
    }
}
