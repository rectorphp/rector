<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PSR4\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfigFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/config/normalize_namespace_without_namespace_config.php');
    }

    protected function getRectorClass(): string
    {
        return NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector::class;
    }
}
