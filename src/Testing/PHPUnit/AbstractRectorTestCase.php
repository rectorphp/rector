<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Psr\Container\ContainerInterface;
use Rector\Application\FileProcessor;
use Rector\Configuration\Option;
use Rector\ContributorTools\Finder\RectorsFinder;
use Rector\Exception\ShouldNotHappenException;
use Rector\HttpKernel\RectorKernel;
use Rector\Testing\Application\EnabledRectorsProvider;
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

    protected function setUp(): void
    {
        $this->fixtureSplitter = new FixtureSplitter($this->getTempPath());

        // defined in phpunit.xml
        if (defined('RECTOR_REPOSITORY') && $this->provideConfig() === '') {
            if (self::$allRectorContainer === null) {
                $this->createContainerWithAllRectors();

                self::$allRectorContainer = self::$container;
            } else {
                // load from cache
                self::$container = self::$allRectorContainer;
            }

            $enabledRectorsProvider = static::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
            $this->configureEnabledRectors($enabledRectorsProvider);
        } elseif ($this->provideConfig() !== '') {
            $this->ensureConfigFileExists();
            $this->bootKernelWithConfigs(RectorKernel::class, [$this->provideConfig()]);

            $enabledRectorsProvider = static::$container->get(EnabledRectorsProvider::class);
            $enabledRectorsProvider->reset();
        } else {
            throw new ShouldNotHappenException();
        }

        // disable any output
        $symfonyStyle = static::$container->get(SymfonyStyle::class);
        $symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->fileProcessor = static::$container->get(FileProcessor::class);
        $this->parameterProvider = static::$container->get(ParameterProvider::class);
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
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        // can be implemented
        return [];
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
            $this->doTestFileMatchesExpectedContent($originalFile, $changedFile, $smartFileInfo->getRealPath());
        }

        $this->autoloadTestFixture = true;
    }

    protected function getTempPath(): string
    {
        return sys_get_temp_dir() . '/rector_temp_tests';
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
        $allRectorClasses = (new RectorsFinder())->findCoreRectorClasses();
        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/all_rectors.yaml');

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

    private function configureEnabledRectors(EnabledRectorsProvider $enabledRectorsProvider): void
    {
        if ($this->getRectorsWithConfiguration() !== []) {
            foreach ($this->getRectorsWithConfiguration() as $rectorClass => $rectorConfiguration) {
                $enabledRectorsProvider->addEnabledRector($rectorClass, $rectorConfiguration);
            }
        } else {
            $enabledRectorsProvider->addEnabledRector($this->getRectorClass(), $this->getRectorConfiguration());
        }
    }
}
