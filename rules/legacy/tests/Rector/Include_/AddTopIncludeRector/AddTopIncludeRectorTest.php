<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\Legacy\Rector\Include_\AddTopIncludeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddTopIncludeRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $temporaryFileInfo = $this->doTestFileInfo($fileInfo);

        $this->assertStringEqualsFile(__DIR__ . '/Expected/expected_autoload.php', $temporaryFileInfo->getContents());
    }

    public function testSkip(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/skip_has_include.php.inc');
        $temporaryFileInfo = $this->doTestFileInfo($fileInfo);
        $this->assertSame($fileInfo->getContents(), $temporaryFileInfo->getContents());

        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/skip_has_class.php.inc');
        $temporaryFileInfo = $this->doTestFileInfo($fileInfo);
        $this->assertSame($fileInfo->getContents(), $temporaryFileInfo->getContents());
    }

    /**
     * @return string[][]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddTopIncludeRector::class => [
                '$type' => 'TYPE_INCLUDE',
                '$file' => "__DIR__ . '/../autoloader.php'",
            ],
        ];
    }
}
