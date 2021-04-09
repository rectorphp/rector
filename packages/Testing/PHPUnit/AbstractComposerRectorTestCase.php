<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\Json;
use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Testing\Contract\ConfigFileAwareInterface;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractComposerRectorTestCase extends AbstractKernelTestCase implements ConfigFileAwareInterface
{
    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    /**
     * @var ComposerModifier
     */
    private $composerModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfigFile()]);

        $this->composerModifier = $this->getService(ComposerModifier::class);
        $this->composerJsonFactory = $this->getService(ComposerJsonFactory::class);
    }

    protected function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $composerJson = $this->composerJsonFactory->createFromFileInfo($inputFileInfoAndExpected->getInputFileInfo());
        $this->composerModifier->modify($composerJson);

        $changedComposerJson = Json::encode($composerJson->getJsonArray(), Json::PRETTY);
        $this->assertJsonStringEqualsJsonString($inputFileInfoAndExpected->getExpected(), $changedComposerJson);
    }
}
