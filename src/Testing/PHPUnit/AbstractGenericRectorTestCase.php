<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Iterator;
use PHPStan\Analyser\NodeScopeResolver;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\Stubs\StubLoader;
use Rector\Core\Testing\Application\EnabledRectorsProvider;
use Rector\Core\Testing\Finder\RectorsFinder;
use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\ContainerInterface;
use Rector\Set\SetProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractGenericRectorTestCase extends AbstractKernelTestCase
{
    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @var SmartFileSystem
     */
    protected $smartFileSystem;

    /**
     * @var NonPhpFileProcessor
     */
    protected $nonPhpFileProcessor;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var RunnableRectorFactory
     */
    protected $runnableRectorFactory;

    /**
     * @var NodeScopeResolver
     */
    protected $nodeScopeResolver;

    /**
     * @var Container|ContainerInterface|null
     */
    protected static $allRectorContainer;

    /**
     * @var mixed[]
     */
    private $oldParameterValues = [];

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

        $this->oldParameterValues = [];
    }

    protected function tearDown(): void
    {
        $this->restoreOldParameterValues();

        // restore PHP version if changed
        if ($this->getPhpVersion() !== '') {
            $this->setParameter(Option::PHP_VERSION_FEATURES, '10.0');
        }
    }

    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    protected function provideConfigFileInfo(): ?SmartFileInfo
    {
        if ($this->provideSet() !== '') {
            $setProvider = new SetProvider();
            $set = $setProvider->provideByName($this->provideSet());
            if ($set === null) {
                $message = sprintf('Invalid set name provided "%s"', $this->provideSet());
                throw new ShouldNotHappenException($message);
            }

            return $set->getSetFileInfo();
        }

        // can be implemented
        return null;
    }

    protected function provideSet(): string
    {
        // can be implemented
        return '';
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        // can be implemented, has the highest priority
        return [];
    }

    /**
     * @return mixed[]|null[]
     */
    protected function getCurrentTestRectorClassesWithConfiguration(): array
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            foreach (array_keys($this->getRectorsWithConfiguration()) as $rectorClass) {
                $this->ensureRectorClassIsValid($rectorClass, 'getRectorsWithConfiguration');
            }

            return $this->getRectorsWithConfiguration();
        }

        $rectorClass = $this->getRectorClass();
        $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

        return [$rectorClass => null];
    }

    /**
     * Return interface type that extends @see \Rector\Core\Contract\Rector\RectorInterface;
     */
    abstract protected function getRectorInterface(): string;

    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        return StaticFixtureFinder::yieldDirectory($directory, $suffix);
    }

    /**
     * @param mixed $value
     */
    protected function setParameter(string $name, $value): void
    {
        $parameterProvider = self::$container->get(ParameterProvider::class);

        if ($name !== Option::PHP_VERSION_FEATURES) {
            $oldParameterValue = $parameterProvider->provideParameter($name);
            $this->oldParameterValues[$name] = $oldParameterValue;
        }

        $parameterProvider->changeParameter($name, $value);
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     */
    protected function bootKernelWithConfigInfos(string $class, array $configFileInfos): KernelInterface
    {
        $configFiles = [];
        foreach ($configFileInfos as $configFileInfo) {
            $configFiles[] = $configFileInfo->getRealPath();
        }

        return $this->bootKernelWithConfigs($class, $configFiles);
    }

    protected function configureEnabledRectors(EnabledRectorsProvider $enabledRectorsProvider): void
    {
        foreach ($this->getCurrentTestRectorClassesWithConfiguration() as $rectorClass => $configuration) {
            $enabledRectorsProvider->addEnabledRector($rectorClass, (array) $configuration);
        }
    }

    protected function createRectorRepositoryContainer(): void
    {
        if (self::$allRectorContainer === null) {
            $this->createContainerWithAllRectors();

            self::$allRectorContainer = self::$container;
            return;
        }

        // load from cache
        self::$container = self::$allRectorContainer;
    }

    protected function getPhpVersion(): string
    {
        // to be implemented
        return '';
    }

    private function restoreOldParameterValues(): void
    {
        if ($this->oldParameterValues === []) {
            return;
        }

        $parameterProvider = self::$container->get(ParameterProvider::class);

        foreach ($this->oldParameterValues as $name => $oldParameterValue) {
            $parameterProvider->changeParameter($name, $oldParameterValue);
        }
    }

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, $this->getRectorInterface(), true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            $this->getRectorInterface()
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

        // @todo PHP dump
        $yamlContent = Yaml::dump([
            'services' => $listForConfig,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/all_rectors.yaml');

        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($configFileTempPath, $yamlContent);

        $this->bootKernelWithConfigs(RectorKernel::class, [$configFileTempPath]);
    }

    private function configurePhpVersionFeatures(): void
    {
        if ($this->getPhpVersion() === '') {
            return;
        }

        $this->setParameter(Option::PHP_VERSION_FEATURES, $this->getPhpVersion());
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
}
