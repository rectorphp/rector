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
        $this->assertFileDoesNotExist($this->getFixtureTempDirectory() . '/Source/SomeFactoryInterface.php');
    }

    protected function getRectorClass(): string
    {
        return DeleteFactoryInterfaceRector::class;
    }
}
