<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileSystem\FunctionToStaticMethodRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\Legacy\Rector\FileSystem\FunctionToStaticMethodRector;

final class FunctionToStaticMethodRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Source/static_functions.php');

        $this->assertFileExists($this->getFixtureTempDirectory() . '/Source/StaticFunctions.php');
        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedStaticFunctions.php',
            $this->getFixtureTempDirectory() . '/Source/StaticFunctions.php'
        );
    }

    protected function getRectorClass(): string
    {
        return FunctionToStaticMethodRector::class;
    }
}
