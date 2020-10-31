<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\AddTopIncludeRector;

use Iterator;
use Rector\Legacy\Rector\FileWithoutNamespace\AddTopIncludeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return array<string, mixed[]>
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
