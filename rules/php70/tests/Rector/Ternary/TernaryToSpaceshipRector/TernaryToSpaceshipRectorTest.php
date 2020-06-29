<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToSpaceshipRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TernaryToSpaceshipRectorTest extends AbstractRectorTestCase
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
        return TernaryToSpaceshipRector::class;
    }
}
