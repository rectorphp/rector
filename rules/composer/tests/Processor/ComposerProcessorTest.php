<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Processor;

use Iterator;
use Rector\Composer\Processor\ComposerProcessor;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ComposerProcessorTest extends AbstractKernelTestCase
{
    /**
     * @var ComposerProcessor
     */
    private $composerProcessor;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->composerProcessor = $this->getService(ComposerProcessor::class);
    }

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
