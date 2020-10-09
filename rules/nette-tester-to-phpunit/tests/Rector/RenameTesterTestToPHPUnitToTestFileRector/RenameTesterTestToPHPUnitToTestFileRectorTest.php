<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\RenameTesterTestToPHPUnitToTestFileRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameTesterTestToPHPUnitToTestFileRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Source/SomeCase.phpt');
        $this->doTestFileInfo($fixtureFileInfo);

        $temporaryFilePath = $this->getFixtureTempDirectory() . '/Source/SomeCase.phpt';

        $this->assertFileMissing($temporaryFilePath);

        $this->assertFileExists($this->getFixtureTempDirectory() . '/Source/SomeCaseTest.php');
    }

    protected function getRectorClass(): string
    {
        return RenameTesterTestToPHPUnitToTestFileRector::class;
    }
}
