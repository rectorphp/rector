<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\SetTest;

use Iterator;
use Rector\Composer\Processor\ComposerProcessor;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture/', '*.json.inc');
    }

    protected function provideConfigFileInfo(): ?SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/set.php');
    }

    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo, array $extraFiles = []): void
    {
        $composerProcessor = $this->getService(ComposerProcessor::class);
        $composerProcessor->process();

        $this->assertFileEquals(__DIR__ . '/Fixture/composer_after.json', __DIR__ . '/Fixture/composer_before.json');
    }
}
