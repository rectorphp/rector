<?php

declare(strict_types=1);

namespace Rector\Tests\Laravel\Rector\New_\AddGuardToLoginEventRector;

use Iterator;
use Rector\Laravel\Rector\New_\AddGuardToLoginEventRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddGuardToLoginEventRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AddGuardToLoginEventRector::class;
    }
}
