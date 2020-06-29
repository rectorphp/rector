<?php

declare(strict_types=1);

namespace Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddMessageToEqualsResponseCodeRectorTest extends AbstractRectorTestCase
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
        return AddMessageToEqualsResponseCodeRector::class;
    }
}
