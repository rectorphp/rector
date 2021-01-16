<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\Rector;

use Nette\Utils\Json;
use Rector\Composer\ComposerModifier;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Testing\Guard\FixtureGuard;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractComposerRectorTestCase extends AbstractKernelTestCase
{
    /**
     * @var FixtureGuard
     */
    private $fixtureGuard;

    /**
     * @var ComposerModifier
     */
    private $composerModifier;

    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfigFile()]);

        $this->fixtureGuard = $this->getService(FixtureGuard::class);
        $this->composerModifier = $this->getService(ComposerModifier::class);
        $this->composerJsonFactory = $this->getService(ComposerJsonFactory::class);
    }

    abstract protected function provideConfigFile(): string;

    protected function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->fixtureGuard->ensureFileInfoHasDifferentBeforeAndAfterContent($smartFileInfo);

        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($smartFileInfo);

        $composerJson = $this->composerJsonFactory->createFromFileInfo($inputFileInfoAndExpected->getInputFileInfo());
        $this->composerModifier->modify($composerJson);

        $changedComposerJson = Json::encode($composerJson->getJsonArray(), Json::PRETTY);
        $this->assertJsonStringEqualsJsonString($inputFileInfoAndExpected->getExpected(), $changedComposerJson);
    }
}
