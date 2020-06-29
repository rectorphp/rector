<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\ClassMethod\ChangeSetIdTypeToUuidRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Doctrine\Rector\ClassMethod\ChangeSetIdTypeToUuidRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSetIdTypeToUuidRectorTest extends AbstractRectorTestCase
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
        return ChangeSetIdTypeToUuidRector::class;
    }
}
