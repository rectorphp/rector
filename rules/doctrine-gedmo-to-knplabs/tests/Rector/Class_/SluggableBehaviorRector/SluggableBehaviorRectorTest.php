<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\SluggableBehaviorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\SluggableBehaviorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SluggableBehaviorRectorTest extends AbstractRectorTestCase
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
        return SluggableBehaviorRector::class;
    }
}
