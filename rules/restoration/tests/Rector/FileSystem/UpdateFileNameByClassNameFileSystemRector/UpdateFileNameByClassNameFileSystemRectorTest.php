<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\Restoration\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UpdateFileNameByClassNameFileSystemRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/different_class_name.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);

        $this->assertFileExists($this->getFixtureTempDirectory() . '/Fixture/CorrectClassName.php');
    }

    protected function getRectorClass(): string
    {
        return UpdateFileNameByClassNameFileSystemRector::class;
    }
}
