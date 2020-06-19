<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\RenameTesterTestToPHPUnitToTestFileRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector;

final class RenameTesterTestToPHPUnitToTestFileRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Source/SomeCase.phpt');

        $this->assertFileDoesNotExist($this->getFixtureTempDirectory() . '/Source/SomeCase.phpt');
        $this->assertFileExists($this->getFixtureTempDirectory() . '/Source/SomeCaseTest.php');
    }

    protected function getRectorClass(): string
    {
        return RenameTesterTestToPHPUnitToTestFileRector::class;
    }
}
