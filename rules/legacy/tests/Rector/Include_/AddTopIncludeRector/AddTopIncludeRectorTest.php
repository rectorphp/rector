<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Legacy\Rector\Include_\AddTopIncludeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddTopIncludeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $this->doTestFileInfo($fixtureFileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddTopIncludeRector::class => [
                AddTopIncludeRector::AUTOLOAD_FILE_PATH => '/../autoloader.php',
            ],
        ];
    }
}
