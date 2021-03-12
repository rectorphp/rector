<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector;

use Iterator;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PrivatizeFinalClassPropertyRectorTest extends AbstractRectorTestCase
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
        return PrivatizeFinalClassPropertyRector::class;
    }
}
