<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;

use Iterator;
use Rector\DoctrineCodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CorrectDefaultTypesOnEntityPropertyRectorTest extends AbstractRectorTestCase
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
        return CorrectDefaultTypesOnEntityPropertyRector::class;
    }
}
