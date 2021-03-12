<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector;

use Iterator;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\LoggableBehaviorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class LoggableBehaviorRectorTest extends AbstractRectorTestCase
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
        return LoggableBehaviorRector::class;
    }
}
