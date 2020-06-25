<?php

declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Cast\RecastingRemovalRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPStan\Rector\Cast\RecastingRemovalRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RecastingRemovalRectorTest extends AbstractRectorTestCase
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
        return RecastingRemovalRector::class;
    }
}
