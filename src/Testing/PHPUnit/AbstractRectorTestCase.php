<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Container\ContainerInterface;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Rector\Core\Testing\Contract\RunnableInterface;
use Rector\Core\Testing\Finder\RectorsFinder;
use Rector\Core\Testing\ValueObject\SplitLine;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractRectorTestCase extends AbstractGenericRectorTestCase
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var SmartFileInfo
     */
    protected $originalTempFileInfo;

    /**
     * @var bool
     */
    private $autoloadTestFixture = true;

    /**
     * @var Container|ContainerInterface|null
     */
    private static $allRectorContainer;

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var RunnableRectorFactory
     */
    private $runnableRectorFactory;

    /**
     * @var NonPhpFileProcessor
     */
    private $nonPhpFileProcessor;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runnableRectorFactory = new RunnableRectorFactory();

        if ($this->provideConfigFileInfo() !== null) {
            $configFileInfos = $this->resolveConfigs($this->provideConfigFileInfo());

            $this->bootKernelWithConfigInfos(RectorKernel::class, $configFileInfos);

            $enabledRectorsProvider = static::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
        } else {
            // prepare container with all rectors
            // cache only rector tests - defined in phpunit.xml
            if (defined('RECTOR_REPOSITORY')) {
                $this->createRectorRepositoryContainer();
            } else {
                // 3rd party
                $configFor3rdPartyTest = $this->getConfigFor3rdPartyTest();
                $this->bootKernelWithConfigs(RectorKernel::class, [$configFor3rdPartyTest]);
            }

            $enabledRectorsProvider = self::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
            $this->configureEnabledRectors($enabledRectorsProvider);
        }

        // disable any output
        $symfonyStyle = static::$container->get(SymfonyStyle::class);
        $symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->fileProcessor = static::$container->get(FileProcessor::class);
        $this->nonPhpFileProcessor = static::$container->get(NonPhpFileProcessor::class);
        $this->parameterProvider = static::$container->get(ParameterProvider::class);
        $this->smartFileSystem = static::$container->get(SmartFileSystem::class);

        // needed for PHPStan, because the analyzed file is just create in /temp
        $this->nodeScopeResolver = static::$container->get(NodeScopeResolver::class);

        // load stubs
        $stubLoader = static::$container->get(StubLoader::class);
        $stubLoader->loadStubs();

        $this->configurePhpVersionFeatures();

        // so the files are removed and added
        $configuration = static::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // restore PHP version if changed
        if ($this->getPhpVersion() !== '') {
            $this->setParameter(Option::PHP_VERSION_FEATURES, '10.0');
        }
    }

    protected function doTestFileInfoWithoutAutoload(SmartFileInfo $fileInfo): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFileInfo($fileInfo);
        $this->autoloadTestFixture = true;
    }

    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo): void
    {
        [$originalFileInfo, $expectedFileInfo] = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo,
            $this->autoloadTestFixture
        );

        $this->nodeScopeResolver->setAnalysedFiles([$originalFileInfo->getRealPath()]);

        $this->doTestFileMatchesExpectedContent($originalFileInfo, $expectedFileInfo, $fixtureFileInfo);

        $this->originalTempFileInfo = $originalFileInfo;

        // runnable?
        if (Strings::contains($originalFileInfo->getContents(), RunnableInterface::class)) {
            $this->assertOriginalAndFixedFileResultEquals($originalFileInfo, $expectedFileInfo);
        }
    }

    protected function assertOriginalAndFixedFileResultEquals(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo
    ): void {
        $runnable = $this->runnableRectorFactory->createRunnableClass($originalFileInfo);
        $expectedInstance = $this->runnableRectorFactory->createRunnableClass($expectedFileInfo);

        $actualResult = $runnable->run();
        $expectedResult = $expectedInstance->run();

        $this->assertSame($expectedResult, $actualResult);
    }

    protected function getTempPath(): string
    {
        return StaticFixtureSplitter::getTemporaryPath();
    }

    protected function getPhpVersion(): string
    {
        // to be implemented
        return '';
    }

    protected function getRectorInterface(): string
    {
        return PhpRectorInterface::class;
    }

    protected function doTestExtraFile(string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $temporaryPath = StaticFixtureSplitter::getTemporaryPath();
        $expectedFilePath = $temporaryPath . '/' . $expectedExtraFileName;
        $this->assertFileExists($expectedFilePath);

        $this->assertFileEquals($expectedExtraContentFilePath, $expectedFilePath);
    }

    private function createContainerWithAllRectors(): void
    {
        $rectorsFinder = new RectorsFinder();
        $coreRectorClasses = $rectorsFinder->findCoreRectorClasses();

        $listForConfig = [];
        foreach ($coreRectorClasses as $rectorClass) {
            $listForConfig[$rectorClass] = null;
        }

        foreach (array_keys($this->getCurrentTestRectorClassesWithConfiguration()) as $rectorClass) {
            $listForConfig[$rectorClass] = null;
        }

        $yamlContent = Yaml::dump([
            'services' => $listForConfig,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/all_rectors.yaml');

        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($configFileTempPath, $yamlContent);

        $this->bootKernelWithConfigs(RectorKernel::class, [$configFileTempPath]);
    }

    private function getConfigFor3rdPartyTest(): string
    {
        $currentTestRectorClassesWithConfiguration = $this->getCurrentTestRectorClassesWithConfiguration();
        $yamlContent = Yaml::dump([
            'services' => $currentTestRectorClassesWithConfiguration,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/current_test.yaml');
        $this->smartFileSystem->dumpFile($configFileTempPath, $yamlContent);

        return $configFileTempPath;
    }

    private function configureEnabledRectors(EnabledRectorsProvider $enabledRectorsProvider): void
    {
        foreach ($this->getCurrentTestRectorClassesWithConfiguration() as $rectorClass => $configuration) {
            $enabledRectorsProvider->addEnabledRector($rectorClass, (array) $configuration);
        }
    }

    private function configurePhpVersionFeatures(): void
    {
        if ($this->getPhpVersion() === '') {
            return;
        }

        $this->setParameter(Option::PHP_VERSION_FEATURES, $this->getPhpVersion());
    }

    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo
    ): void {
        $this->setParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        if ($originalFileInfo->getSuffix() === 'php') {
            // life-cycle trio :)
            $this->fileProcessor->parseFileInfoToLocalCache($originalFileInfo);
            $this->fileProcessor->refactor($originalFileInfo);

            $this->fileProcessor->postFileRefactor($originalFileInfo);

            // mimic post-rectors
            $changedContent = $this->fileProcessor->printToString($originalFileInfo);

            $removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
            $removedAndAddedFilesProcessor->run();
        } elseif (in_array($originalFileInfo->getSuffix(), StaticNonPhpFileSuffixes::SUFFIXES, true)) {
            $changedContent = $this->nonPhpFileProcessor->processFileInfo($originalFileInfo);
        } else {
            $message = sprintf('Suffix "%s" is not supported yet', $originalFileInfo->getSuffix());
            throw new ShouldNotHappenException($message);
        }

        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();

        try {
            $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
        } catch (ExpectationFailedException $expectationFailedException) {
            $contents = $expectedFileInfo->getContents();

            $this->updateFixtureContent($originalFileInfo, $changedContent, $fixtureFileInfo);

            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent, $relativeFilePathFromCwd);
        }
    }

    private function createRectorRepositoryContainer(): void
    {
        if (self::$allRectorContainer === null) {
            $this->createContainerWithAllRectors();

            self::$allRectorContainer = self::$container;
            return;
        }

        // load from cache
        self::$container = self::$allRectorContainer;
    }

    /**
     * @return SmartFileInfo[]
     */
    private function resolveConfigs(SmartFileInfo $configFileInfo): array
    {
        $configFileInfos = [$configFileInfo];

        $rectorConfigsResolver = new RectorConfigsResolver();
        $setFileInfos = $rectorConfigsResolver->resolveSetFileInfosFromConfigFileInfos($configFileInfos);

        return array_merge($configFileInfos, $setFileInfos);
    }

    private function updateFixtureContent(
        SmartFileInfo $originalFileInfo,
        string $changedContent,
        SmartFileInfo $fixtureFileInfo
    ): void {
        if (! getenv('UPDATE_TESTS') && ! getenv('UT')) {
            return;
        }

        $newOriginalContent = $this->resolveNewFixtureContent($originalFileInfo, $changedContent);
        $this->smartFileSystem->dumpFile($fixtureFileInfo->getRealPath(), $newOriginalContent);
    }

    private function resolveNewFixtureContent(SmartFileInfo $originalFileInfo, string $changedContent): string
    {
        if ($originalFileInfo->getContents() === $changedContent) {
            return $originalFileInfo->getContents();
        }

        return $originalFileInfo->getContents() . SplitLine::LINE . $changedContent;
    }
}
