<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Iterator;
use Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\Testing\Application\EnabledRectorsProvider;
use Rector\Testing\Contract\RunnableInterface;
use Rector\Testing\Finder\RectorsFinder;
use Rector\Testing\Guard\FixtureGuard;
use Rector\Testing\PhpConfigPrinter\PhpConfigPrinterFactory;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
use Rector\Testing\ValueObject\InputFilePathWithExpectedFile;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractRectorTestCase extends AbstractKernelTestCase
{
    use MovingFilesTrait;
<<<<<<< HEAD
=======

    /**
<<<<<<< HEAD
     * @var int
     */
    private const PHP_VERSION_UNDEFINED = 0;
>>>>>>> 77c4fbe09... cleanup

    /**
=======
>>>>>>> b1833ea1a... use php version right in config
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var SmartFileSystem
     */
    protected static $smartFileSystem;

    /**
     * @var NonPhpFileProcessor
     */
    protected $nonPhpFileProcessor;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
<<<<<<< HEAD
=======
     * @var RunnableRectorFactory
     */
    protected $runnableRectorFactory;

    /**
>>>>>>> b1833ea1a... use php version right in config
     * @var FixtureGuard
     */
    protected static $fixtureGuard;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;

    /**
     * @var SmartFileInfo
     */
    protected $originalTempFileInfo;

    /**
     * @var SmartFileInfo
     */
    protected static $allRectorContainer;

    /**
     * @var bool
     */
    private static $isInitialized = false;

    /**
     * @var RunnableRectorFactory
     */
    private static $runnableRectorFactory;

    /**
<<<<<<< HEAD
     * @var bool
     */
    private $autoloadTestFixture = true;

    /**
=======
>>>>>>> 495b7788a... use more configs
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var RectorConfigsResolver
     */
    private static $rectorConfigsResolver;

    protected function setUp(): void
    {
        $this->initializeDependencies();

        if ($this->provideConfigFileInfo() !== null) {
            $configFileInfos = self::$rectorConfigsResolver->resolveFromConfigFileInfo($this->provideConfigFileInfo());

            $this->bootKernelWithConfigsAndStaticCache(RectorKernel::class, $configFileInfos);

            $enabledRectorsProvider = $this->getService(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
        } else {
            // prepare container with all rectors
            // cache only rector tests - defined in phpunit.xml
            if (defined('RECTOR_REPOSITORY')) {
                $this->createRectorRepositoryContainer();
            } else {
                // boot core config, where 3rd party services might be loaded
                $rootRectorPhp = getcwd() . '/rector.php';
                $configs = [];

                if (file_exists($rootRectorPhp)) {
                    $configs[] = $rootRectorPhp;
                }

                // 3rd party
                $configs[] = $this->getConfigFor3rdPartyTest();
                $this->bootKernelWithConfigs(RectorKernel::class, $configs);
            }

            $enabledRectorsProvider = $this->getService(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
            $enabledRectorsProvider->addEnabledRector($this->getRectorClass(), []);
        }

        $this->fileProcessor = $this->getService(FileProcessor::class);
        $this->nonPhpFileProcessor = $this->getService(NonPhpFileProcessor::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
        $this->removedAndAddedFilesCollector = $this->getService(RemovedAndAddedFilesCollector::class);
        $this->removedAndAddedFilesCollector->reset();
<<<<<<< HEAD
<<<<<<< HEAD
=======

        // needed for PHPStan, because the analyzed file is just create in /temp
        $this->nodeScopeResolver = $this->getService(NodeScopeResolver::class);

        $this->configurePhpVersionFeatures();
>>>>>>> 495b7788a... use more configs
=======
>>>>>>> b1833ea1a... use php version right in config
    }

    /**
     * @return class-string<RectorInterface>
     */
    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    protected function provideConfigFileInfo(): ?SmartFileInfo
    {
        // can be implemented
        return null;
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

<<<<<<< HEAD
<<<<<<< HEAD
=======
    protected function getPhpVersion(): int
    {
        // default value to be implemented for testing lower versions
        return PhpVersion::PHP_10;
    }

>>>>>>> 495b7788a... use more configs
=======
>>>>>>> b1833ea1a... use php version right in config
    protected function doTestFileInfoWithoutAutoload(SmartFileInfo $fileInfo): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFileInfo($fileInfo);
        $this->autoloadTestFixture = true;
    }

    /**
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo, array $extraFiles = []): void
    {
        self::$fixtureGuard->ensureFileInfoHasDifferentBeforeAndAfterContent($fixtureFileInfo);

        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo,
            $this->autoloadTestFixture
        );

        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();

        // needed for PHPStan, because the analyzed file is just create in /temp
        /** @var NodeScopeResolver $nodeScopeResolver */
        $nodeScopeResolver = $this->getService(NodeScopeResolver::class);
        $nodeScopeResolver->setAnalysedFiles([$inputFileInfo->getRealPath()]);

        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();

        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo, $extraFiles);
        $this->originalTempFileInfo = $inputFileInfo;

        // runnable?
        if (! file_exists($inputFileInfo->getPathname())) {
            return;
        }

        if (! Strings::contains($inputFileInfo->getContents(), RunnableInterface::class)) {
            return;
        }

        $this->assertOriginalAndFixedFileResultEquals($inputFileInfo, $expectedFileInfo);
    }

    protected function getTempPath(): string
    {
        return StaticFixtureSplitter::getTemporaryPath();
    }

    protected function doTestExtraFile(string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $addedFilesWithContents = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();
        foreach ($addedFilesWithContents as $addedFilesWithContent) {
            if (! Strings::endsWith($addedFilesWithContent->getFilePath(), $expectedExtraFileName)) {
                continue;
            }

            $this->assertStringEqualsFile($expectedExtraContentFilePath, $addedFilesWithContent->getFileContent());
            return;
        }

        $addedFilesWithNodes = $this->removedAndAddedFilesCollector->getAddedFilesWithNodes();
        foreach ($addedFilesWithNodes as $addedFileWithNodes) {
            if (! Strings::endsWith($addedFileWithNodes->getFilePath(), $expectedExtraFileName)) {
                continue;
            }

            $printedFileContent = $this->betterStandardPrinter->prettyPrintFile($addedFileWithNodes->getNodes());
            $this->assertStringEqualsFile($expectedExtraContentFilePath, $printedFileContent);
            return;
        }

        $movedFilesWithContent = $this->removedAndAddedFilesCollector->getMovedFileWithContent();
        foreach ($movedFilesWithContent as $movedFileWithContent) {
            if (! Strings::endsWith($movedFileWithContent->getNewPathname(), $expectedExtraFileName)) {
                continue;
            }

            $this->assertStringEqualsFile($expectedExtraContentFilePath, $movedFileWithContent->getFileContent());
            return;
        }

        throw new ShouldNotHappenException();
    }

    protected function getFixtureTempDirectory(): string
    {
        return sys_get_temp_dir() . '/_temp_fixture_easy_testing';
    }

    protected function assertOriginalAndFixedFileResultEquals(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo
    ): void {
<<<<<<< HEAD
        $runnable = self::$runnableRectorFactory->createRunnableClass($originalFileInfo);
        $expectedInstance = self::$runnableRectorFactory->createRunnableClass($expectedFileInfo);
=======
        $runnable = $this->runnableRectorFactory->createRunnableClass($originalFileInfo);
        $expectedInstance = $this->runnableRectorFactory->createRunnableClass($expectedFileInfo);

        $actualResult = $runnable->run();

        $expectedResult = $expectedInstance->run();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return SmartFileInfo[]
     */
    private function resolveConfigs(SmartFileInfo $configFileInfo): array
    {
        $configFileInfos = [$configFileInfo];
>>>>>>> 77c4fbe09... cleanup

        $actualResult = $runnable->run();

        $expectedResult = $expectedInstance->run();
        $this->assertSame($expectedResult, $actualResult);
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

    private function getConfigFor3rdPartyTest(): string
    {
        $filePath = sys_get_temp_dir() . '/rector_temp_tests/current_test.php';
        $this->createPhpConfigFileAndDumpToPath([
            $this->getRectorClass() => [],
        ], $filePath);

        return $filePath;
    }

<<<<<<< HEAD
=======
    private function configureEnabledRectors(EnabledRectorsProvider $enabledRectorsProvider): void
    {
        foreach ($this->getCurrentTestRectorClassesWithConfiguration() as $rectorClass => $configuration) {
            $enabledRectorsProvider->addEnabledRector($rectorClass, (array) $configuration);
        }
    }

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, PhpRectorInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            PhpRectorInterface::class
        ));
    }

>>>>>>> 495b7788a... use more configs
    /**
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo,
        array $extraFiles = []
    ): void {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        if (! Strings::endsWith($originalFileInfo->getFilename(), '.blade.php') && in_array(
            $originalFileInfo->getSuffix(),
            ['php', 'phpt'],
            true
        )) {
            if ($extraFiles === []) {
                $this->fileProcessor->parseFileInfoToLocalCache($originalFileInfo);
                $this->fileProcessor->refactor($originalFileInfo);
                $this->fileProcessor->postFileRefactor($originalFileInfo);
            } else {
                $fileInfosToProcess = [$originalFileInfo];

                foreach ($extraFiles as $extraFile) {
                    $fileInfosToProcess[] = $extraFile->getInputFileInfo();
                }

                // life-cycle trio :)
                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->parseFileInfoToLocalCache($fileInfoToProcess);
                }

                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->refactor($fileInfoToProcess);
                }

                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->postFileRefactor($fileInfoToProcess);
                }
            }

            // mimic post-rectors
            $changedContent = $this->fileProcessor->printToString($originalFileInfo);
        } elseif (Strings::match($originalFileInfo->getFilename(), StaticNonPhpFileSuffixes::getSuffixRegexPattern())) {
            $changedContent = $this->nonPhpFileProcessor->processFileInfo($originalFileInfo);
        } else {
            $message = sprintf('Suffix "%s" is not supported yet', $originalFileInfo->getSuffix());
            throw new ShouldNotHappenException($message);
        }

        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();

        try {
            $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
        } catch (ExpectationFailedException $expectationFailedException) {
            StaticFixtureUpdater::updateFixtureContent($originalFileInfo, $changedContent, $fixtureFileInfo);
            $contents = $expectedFileInfo->getContents();

            // make sure we don't get a diff in which every line is different (because of differences in EOL)
            $contents = $this->normalizeNewlines($contents);

            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent, $relativeFilePathFromCwd);
        }
    }

    private function createContainerWithAllRectors(): void
    {
        $rectorsFinder = new RectorsFinder();
        $coreRectorClasses = $rectorsFinder->findCoreRectorClasses();

        $listForConfig = [];

        foreach ($coreRectorClasses as $rectorClass) {
            $listForConfig[$rectorClass] = null;
        }

        $listForConfig[$this->getRectorClass()] = null;

        $filePath = sys_get_temp_dir() . '/rector_temp_tests/all_rectors.php';
        $this->createPhpConfigFileAndDumpToPath($listForConfig, $filePath);

        $this->bootKernelWithConfigs(RectorKernel::class, [$filePath]);
    }

    /**
     * @param array<string, mixed[]|null> $rectorClassesWithConfiguration
     */
    private function createPhpConfigFileAndDumpToPath(array $rectorClassesWithConfiguration, string $filePath): void
    {
        $phpConfigPrinterFactory = new PhpConfigPrinterFactory();
        $smartPhpConfigPrinter = $phpConfigPrinterFactory->create();

        $fileContent = $smartPhpConfigPrinter->printConfiguredServices($rectorClassesWithConfiguration);
        self::$smartFileSystem->dumpFile($filePath, $fileContent);
    }

    private function normalizeNewlines(string $string): string
    {
        return Strings::replace($string, '#\r\n|\r|\n#', "\n");
    }

    /**
     * Static to avoid reboot on each data fixture
     */
    private function initializeDependencies(): void
    {
        if (self::$isInitialized) {
            return;
        }

        self::$runnableRectorFactory = new RunnableRectorFactory();
        self::$smartFileSystem = new SmartFileSystem();
        self::$fixtureGuard = new FixtureGuard();
        self::$rectorConfigsResolver = new RectorConfigsResolver();

        // load stubs
        $stubLoader = new StubLoader();
        $stubLoader->loadStubs();

        self::$isInitialized = true;
    }
}
