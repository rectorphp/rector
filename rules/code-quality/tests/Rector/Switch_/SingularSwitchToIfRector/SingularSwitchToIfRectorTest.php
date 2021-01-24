<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Switch_\SingularSwitchToIfRector;

use Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SingularSwitchToIfRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SingularSwitchToIfRector::class;
    }
}
