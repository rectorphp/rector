<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\FIleSystem\DeleteFactoryInterfaceRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\NetteToSymfony\Rector\FileSystem\DeleteFactoryInterfaceRector;

final class DeleteFactoryInterfaceFileSystemRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Source/SomeFactoryInterface.php');

        $temporaryFilePath = $this->getFixtureTempDirectory() . '/Source/SomeFactoryInterface.php';

        // PHPUnit 9.0 ready
        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($temporaryFilePath);
        } else {
            // PHPUnit 8.0 ready
            $this->assertFileNotExists($temporaryFilePath);
        }
    }

    protected function getRectorClass(): string
    {
        return DeleteFactoryInterfaceRector::class;
    }
}
