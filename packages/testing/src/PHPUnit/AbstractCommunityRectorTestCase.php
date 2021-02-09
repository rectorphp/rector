<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Iterator;
use PHPStan\Analyser\NodeScopeResolver;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Option;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Testing\Contract\CommunityRectorTestCaseInterface;
use Rector\Testing\Guard\FixtureGuard;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractCommunityRectorTestCase extends AbstractKernelTestCase implements CommunityRectorTestCaseInterface
{
    /**
     * @var BetterStandardPrinter
     */
    public $betterStandardPrinter;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var bool
     */
    private static $isInitialized = false;

    /**
     * @var FixtureGuard
     */
    private static $fixtureGuard;

    /**
     * @var RectorConfigsResolver
     */
    private static $rectorConfigsResolver;

    protected function setUp(): void
    {
        $this->initializeDependencies();

        $smartFileInfo = new SmartFileInfo($this->provideConfigFilePath());
        $configFileInfos = self::$rectorConfigsResolver->resolveFromConfigFileInfo($smartFileInfo);

        $this->bootKernelWithConfigs(RectorKernel::class, $configFileInfos);

        $this->fileProcessor = $this->getService(FileProcessor::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
    }

    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo): void
    {
        self::$fixtureGuard->ensureFileInfoHasDifferentBeforeAndAfterContent($fixtureFileInfo);

        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo
        );

        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();

        // needed for PHPStan, because the analyzed file is just create in /temp
        /** @var NodeScopeResolver $nodeScopeResolver */
        $nodeScopeResolver = $this->getService(NodeScopeResolver::class);
        $nodeScopeResolver->setAnalysedFiles([$inputFileInfo->getRealPath()]);

        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();
        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo);
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo
    ): void {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        $this->fileProcessor->parseFileInfoToLocalCache($originalFileInfo);
        $this->fileProcessor->refactor($originalFileInfo);
        $this->fileProcessor->postFileRefactor($originalFileInfo);

        // mimic post-rectors
        $changedContent = $this->fileProcessor->printToString($originalFileInfo);
        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();
        $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
    }

    private function initializeDependencies(): void
    {
        if (self::$isInitialized) {
            return;
        }

        self::$fixtureGuard = new FixtureGuard();
        self::$rectorConfigsResolver = new RectorConfigsResolver();

        self::$isInitialized = true;
    }
}
