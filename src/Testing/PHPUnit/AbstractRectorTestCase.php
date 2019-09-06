<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use PHPStan\Analyser\NodeScopeResolver;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\Configuration\Option;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\HttpKernel\RectorKernel;
use Rector\Testing\Application\EnabledRectorsProvider;
use Rector\Testing\Finder\RectorsFinder;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractRectorTestCase extends AbstractKernelTestCase
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
        $this->fixtureSplitter = new FixtureSplitter($this->getTempPath());

        // defined in phpunit.xml
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
                $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfig()]);
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

        $this->configurePhpVersionFeatures();
    }

    protected function tearDown(): void
    {
        // restore PHP version
        if ($this->getPhpVersion()) {
            $parameterProvider = self::$container->get(ParameterProvider::class);
            $parameterProvider->changeParameter('php_version_features', '10.0');
        }
    }

    /**
     * @param mixed[] $files
     */
    public function doTestFilesWithoutAutoload(array $files): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFiles($files);
    }

    protected function provideConfig(): string
    {
        // can be implemented
        return '';
    }

    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    /**
     * @return array<string, array>
     */
    protected function getRectorsWithConfiguration(): array
    {
        // can be implemented, has the highest priority
        return [];
    }

    /**
     * @param string[] $files
     */
    protected function doTestFiles(array $files): void
    {
        // 1. original to changed content
        foreach ($files as $file) {
            $smartFileInfo = new SmartFileInfo($file);
            [$originalFile, $changedFile] = $this->fixtureSplitter->splitContentToOriginalFileAndExpectedFile(
                $smartFileInfo,
                $this->autoloadTestFixture
            );

            $this->nodeScopeResolver->setAnalysedFiles([$originalFile]);

            $this->doTestFileMatchesExpectedContent($originalFile, $changedFile, $smartFileInfo->getRealPath());
        }

        $this->autoloadTestFixture = true;
    }

    protected function doTestFile(string $file): void
    {
        $this->doTestFiles([$file]);
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

    private function doTestFileMatchesExpectedContent(
        string $originalFile,
        string $expectedFile,
        string $fixtureFile
    ): void {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFile]);

        $smartFileInfo = new SmartFileInfo($originalFile);

        // life-cycle trio :)
        $this->fileProcessor->parseFileInfoToLocalCache($smartFileInfo);
        $this->fileProcessor->refactor($smartFileInfo);
        $changedContent = $this->fileProcessor->printToString($smartFileInfo);

        $this->assertStringEqualsFile($expectedFile, $changedContent, 'Caused by ' . $fixtureFile);
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
        $coreRectorClasses = (new RectorsFinder())->findCoreRectorClasses();

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/all_rectors.yaml');

        $allRectorClasses = array_merge($coreRectorClasses, $this->getCurrentTestRectorClasses());

        $listForConfig = [];
        foreach ($allRectorClasses as $rectorClass) {
            $listForConfig[$rectorClass] = null;
        }

        $yamlContent = Yaml::dump([
            'services' => $listForConfig,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        FileSystem::write($configFileTempPath, $yamlContent);

        $configFile = $configFileTempPath;
        $this->bootKernelWithConfigs(RectorKernel::class, [$configFile]);
    }

    /**
     * @return string[]
     */
    private function getCurrentTestRectorClasses(): array
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            return array_keys($this->getRectorsWithConfiguration());
        }

        $rectorClass = $this->getRectorClass();
        $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

        return [$rectorClass];
    }

    private function configureEnabledRectors(EnabledRectorsProvider $enabledRectorsProvider): void
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            foreach ($this->getRectorsWithConfiguration() as $rectorClass => $rectorConfiguration) {
                $this->ensureRectorClassIsValid($rectorClass, 'getRectorsWithConfiguration');

                $enabledRectorsProvider->addEnabledRector($rectorClass, $rectorConfiguration);
            }
        } else {
            $rectorClass = $this->getRectorClass();
            $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

            $enabledRectorsProvider->addEnabledRector($rectorClass, []);
        }
    }

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, RectorInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            RectorInterface::class
        ));
    }

    private function configurePhpVersionFeatures(): void
    {
        if ($this->getPhpVersion() === '') {
            return;
        }

        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter('php_version_features', $this->getPhpVersion());
    }
}
