<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\Restoration\Rector\FileSystem\UpdateFileNameByClassNameFileSystemRector;

final class UpdateFileNameByClassNameFileSystemRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/different_class_name.php.inc');
        $this->assertFileExists($this->getFixtureTempDirectory() . '/Fixture/CorrectClassName.php');
    }

    protected function getRectorClass(): string
    {
        return UpdateFileNameByClassNameFileSystemRector::class;
    }
}
