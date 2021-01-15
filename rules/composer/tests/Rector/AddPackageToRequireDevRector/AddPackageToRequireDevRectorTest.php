<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Rector\AddPackageToRequireDevRector;

use Iterator;
use Rector\Composer\Tests\Rector\AbstractComposerRectorTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddPackageToRequireDevRectorTest extends AbstractComposerRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        dump($fileInfo);
        die;
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.json');
    }

    protected function provideConfigFile(): string
    {
        return __DIR__ . '/config/some_config.php';
    }
}
