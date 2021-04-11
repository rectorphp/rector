<?php

declare(strict_types=1);

namespace Rector\Tests\Composer\Rector\ReplacePackageAndVersionComposerRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplacePackageAndVersionComposerRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.json');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/some_config.php';
    }
}
