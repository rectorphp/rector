<?php

declare(strict_types=1);

namespace Rector\Tests\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascal;

use Iterator;
use Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSnakedFixtureNameToPascalTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ChangeSnakedFixtureNameToPascalRector::class;
    }
}
