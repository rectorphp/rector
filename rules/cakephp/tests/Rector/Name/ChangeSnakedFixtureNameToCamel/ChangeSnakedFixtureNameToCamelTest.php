<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\Name\ChangeSnakedFixtureNameToCamel;

use Iterator;
use Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSnakedFixtureNameToCamelTest extends AbstractRectorTestCase
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
        return ChangeSnakedFixtureNameToCamelRector::class;
    }
}
