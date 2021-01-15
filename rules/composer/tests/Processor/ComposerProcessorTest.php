<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Processor;

use Rector\Composer\Processor\ComposerProcessor;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ComposerProcessorTest extends AbstractKernelTestCase
{
    /**
     * @var ComposerProcessor
     */
    private $composerProcessor;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/configured_composer_processor.php']);
        $this->composerProcessor = $this->getService(ComposerProcessor::class);
    }

    public function test(): void
    {
        $this->composerProcessor->process();
        $this->assertFileEquals(
            __DIR__ . '/Fixture/composer_after.json',
            __DIR__ . '/Fixture/composer_before.json'
        );
    }
}
