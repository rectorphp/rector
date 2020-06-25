<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PrivatizeFinalClassPropertyRectorTest extends AbstractRectorTestCase
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
        return PrivatizeFinalClassPropertyRector::class;
    }
}
