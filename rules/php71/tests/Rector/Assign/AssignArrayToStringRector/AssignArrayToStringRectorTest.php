<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AssignArrayToStringRectorTest extends AbstractRectorTestCase
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
        return AssignArrayToStringRector::class;
    }
}
