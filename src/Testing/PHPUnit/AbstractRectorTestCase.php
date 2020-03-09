<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Container\ContainerInterface;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Rector\Core\Testing\Finder\RectorsFinder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

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
     * @var bool
     */
    private $autoloadTestFixture = true;

    /**
     * @var FixtureSplitter
     */
    private $fixtureSplitter;

    /**
     * @var Container|ContainerInterface|null
     */
    private static $allRectorContainer;

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureSplitter = new FixtureSplitter($this->getTempPath());

        if ($this->provideConfig() !== '') {
            $this->ensureConfigFileExists();
            $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfig()]);

            $enabledRectorsProvider = static::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
        } else {
            // repare contains with all rectors
            // cache only rector tests - defined in phpunit.xml
            if (defined('RECTOR_REPOSITORY')) {
                if (self::$allRectorContainer === null) {
                    $this->createContainerWithAllRectors();

                    self::$allRectorContainer = self::$container;
                } else {
                    // load from cache
                    self::$container = self::$allRectorContainer;
                }
            } else {
                // 3rd party
                $configFileTempPath = $this->getConfigFor3rdPartyTest();
                $this->bootKernelWithConfigs(RectorKernel::class, [$configFileTempPath]);
            }

            $enabledRectorsProvider = self::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
            $this->configureEnabledRectors($enabledRectorsProvider);
        }

        // disable any output
        $symfonyStyle = static::$container->get(SymfonyStyle::class);
        $symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->fileProcessor = static::$container->get(FileProcessor::class);
        $this->parameterProvider = static::$container->get(ParameterProvider::class);

        // needed for PHPStan, because the analyzed file is just create in /temp
        $this->nodeScopeResolver = static::$container->get(NodeScopeResolver::class);

        // load stubs
        $stubLoader = static::$container->get(StubLoader::class);
        $stubLoader->loadStubs();

        $this->configurePhpVersionFeatures();
        $this->configureAutoImportParameter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // restore PHP version if changed
        if ($this->getPhpVersion() !== '') {
            $this->setParameter(Option::PHP_VERSION_FEATURES, '10.0');
        }

        // restore disabled auto_import_names if changed
        if ($this->getAutoImportNames() !== null) {
            $this->setParameter(Option::AUTO_IMPORT_NAMES, false);
        }
    }

    protected function doTestFileWithoutAutoload(string $file): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFile($file);
        $this->autoloadTestFixture = true;
    }

    protected function provideConfig(): string
    {
        // can be implemented
        return '';
    }

    protected function doTestFile(string $file): void
    {
        $smartFileInfo = new SmartFileInfo($file);
        [$originalFile, $changedFile] = $this->fixtureSplitter->splitContentToOriginalFileAndExpectedFile(
            $smartFileInfo,
            $this->autoloadTestFixture
        );

        $this->nodeScopeResolver->setAnalysedFiles([$originalFile]);
        $this->doTestFileMatchesExpectedContent($originalFile, $changedFile, $smartFileInfo->getRealPath());
    }

    protected function getTempPath(): string
    {
        return sys_get_temp_dir() . '/rector_temp_tests';
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

    protected function getAutoImportNames(): ?bool
    {
        // to be implemented
        return null;
    }

    protected function doTestExtraFile(string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $expectedFilePath = sys_get_temp_dir() . '/rector_temp_tests/' . $expectedExtraFileName;
        $this->assertFileExists($expectedFilePath);

        $this->assertFileEquals($expectedExtraContentFilePath, $expectedFilePath);
    }

    private function ensureConfigFileExists(): void
    {
        if (file_exists($this->provideConfig())) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Config "%s" for test "%s" was not found',
            $this->provideConfig(),
            static::class
        ));
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
        FileSystem::write($configFileTempPath, $yamlContent);

        $this->bootKernelWithConfigs(RectorKernel::class, [$configFileTempPath]);
    }

    private function getConfigFor3rdPartyTest(): string
    {
        if ($this->provideConfig() !== '') {
            return $this->provideConfig();
        }

        $rectorClassWithConfiguration = $this->getCurrentTestRectorClassesWithConfiguration();
        $yamlContent = Yaml::dump([
            'services' => $rectorClassWithConfiguration,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/current_test.yaml');
        FileSystem::write($configFileTempPath, $yamlContent);

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

    private function configureAutoImportParameter(): void
    {
        // for faster tests
        $autoImportNames = false;
        if ($this->getAutoImportNames() !== null) {
            $autoImportNames = $this->getAutoImportNames();
        }

        $this->parameterProvider->changeParameter(Option::AUTO_IMPORT_NAMES, $autoImportNames);
    }

    private function doTestFileMatchesExpectedContent(
        string $originalFile,
        string $expectedFile,
        string $fixtureFile
    ): void {
        $this->setParameter(Option::SOURCE, [$originalFile]);

        $smartFileInfo = new SmartFileInfo($originalFile);

        // life-cycle trio :)
        $this->fileProcessor->parseFileInfoToLocalCache($smartFileInfo);
        $this->fileProcessor->refactor($smartFileInfo);
        $changedContent = $this->fileProcessor->printToString($smartFileInfo);

        try {
            $this->assertStringEqualsFile($expectedFile, $changedContent, 'Caused by ' . $fixtureFile);
        } catch (ExpectationFailedException $expectationFailedException) {
            $expectedFileContent = FileSystem::read($expectedFile);
            $this->assertStringMatchesFormat($expectedFileContent, $changedContent, 'Caused by ' . $fixtureFile);
        }
    }
}
