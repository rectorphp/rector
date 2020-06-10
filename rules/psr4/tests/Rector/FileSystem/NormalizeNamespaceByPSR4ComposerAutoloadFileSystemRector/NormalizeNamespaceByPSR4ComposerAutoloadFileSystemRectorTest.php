<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\PSR4\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector;

final class NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/namespace_less_class.php.inc');
        $this->assertFileExists($this->getFixtureTempDirectory() . '/Fixture/namespace_less_class.php.inc');

        $this->assertFileEquals(
            __DIR__ . '/Source/ExpectedNamespaceLessClass.php',
            $this->getFixtureTempDirectory() . '/Fixture/namespace_less_class.php.inc'
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/normalize_namespace_without_namespace_config.yaml';
    }

    protected function getRectorClass(): string
    {
        return NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector::class;
    }
}
