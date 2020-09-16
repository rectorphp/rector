<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\PropertyProperty\RenamePropertyUnderscoreToCamelCaseRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Naming\Rector\PropertyProperty\RenamePropertyUnderscoreToCamelCaseRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenamePropertyUnderscoreToCamelCaseRectorTest extends AbstractRectorTestCase
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
        return RenamePropertyUnderscoreToCamelCaseRector::class;
    }
}
