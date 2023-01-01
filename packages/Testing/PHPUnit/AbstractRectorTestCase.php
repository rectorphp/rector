<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use Iterator;
use RectorPrefix202301\Nette\Utils\FileSystem;
use RectorPrefix202301\Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use RectorPrefix202301\Psr\Container\ContainerInterface;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\Fixture\FixtureFileFinder;
use Rector\Testing\Fixture\FixtureFileUpdater;
use Rector\Testing\Fixture\FixtureSplitter;
use Rector\Testing\Fixture\FixtureTempFileDumper;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
abstract class AbstractRectorTestCase extends \Rector\Testing\PHPUnit\AbstractTestCase implements RectorTestInterface
{
    use MovingFilesTrait;
    /**
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    protected $parameterProvider;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;
    /**
     * @var string|null
     */
    protected $originalTempFilePath;
    /**
     * @var \Psr\Container\ContainerInterface|null
     */
    protected static $allRectorContainer;
    /**
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    /**
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    protected function setUp() : void
    {
        // speed up
        @\ini_set('memory_limit', '-1');
        $this->includePreloadFilesAndScoperAutoload();
        $configFile = $this->provideConfigFilePath();
        $this->bootFromConfigFiles([$configFile]);
        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->dynamicSourceLocatorProvider = $this->getService(DynamicSourceLocatorProvider::class);
        // restore added and removed files to 0
        $this->removedAndAddedFilesCollector = $this->getService(RemovedAndAddedFilesCollector::class);
        $this->removedAndAddedFilesCollector->reset();
        /** @var AdditionalAutoloader $additionalAutoloader */
        $additionalAutoloader = $this->getService(AdditionalAutoloader::class);
        $additionalAutoloader->autoloadPaths();
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $this->getService(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
    }
    protected function tearDown() : void
    {
        // free memory and trigger gc to reduce memory peak consumption on windows
        unset($this->applicationFileProcessor, $this->parameterProvider, $this->dynamicSourceLocatorProvider, $this->removedAndAddedFilesCollector, $this->originalTempFilePath);
        \gc_collect_cycles();
    }
    /**
     * @return Iterator<<string>>
     */
    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        return FixtureFileFinder::yieldDirectory($directory, $suffix);
    }
    protected function isWindows() : bool
    {
        return \strncasecmp(\PHP_OS, 'WIN', 3) === 0;
    }
    protected function doTestFile(string $fixtureFilePath) : void
    {
        $fixtureFileContents = FileSystem::read($fixtureFilePath);
        if (Strings::match($fixtureFileContents, FixtureSplitter::SPLIT_LINE_REGEX)) {
            // changed content
            [$inputFileContents, $expectedFileContents] = FixtureSplitter::loadFileAndSplitInputAndExpected($fixtureFilePath);
        } else {
            // no change
            $inputFileContents = $fixtureFileContents;
            $expectedFileContents = $fixtureFileContents;
        }
        $fileSuffix = $this->resolveOriginalFixtureFileSuffix($fixtureFilePath);
        $inputFilePath = FixtureTempFileDumper::dump($inputFileContents, $fileSuffix);
        $expectedFilePath = FixtureTempFileDumper::dump($expectedFileContents, $fileSuffix);
        $this->originalTempFilePath = $inputFilePath;
        $this->doTestFileMatchesExpectedContent($inputFilePath, $expectedFilePath, $fixtureFilePath);
    }
    protected function getFixtureTempDirectory() : string
    {
        return FixtureTempFileDumper::getTempDirectory();
    }
    private function resolveExpectedContents(string $filePath) : string
    {
        $contents = FileSystem::read($filePath);
        // make sure we don't get a diff in which every line is different (because of differences in EOL)
        return \str_replace("\r\n", "\n", $contents);
    }
    private function resolveOriginalFixtureFileSuffix(string $filePath) : string
    {
        if (\substr_compare($filePath, '.inc', -\strlen('.inc')) === 0) {
            $filePath = \rtrim($filePath, '.inc');
        }
        if (\substr_compare($filePath, '.blade.php', -\strlen('.blade.php')) === 0) {
            return 'blade.php';
        }
        return \pathinfo($filePath, \PATHINFO_EXTENSION);
    }
    private function includePreloadFilesAndScoperAutoload() : void
    {
        if (\file_exists(__DIR__ . '/../../../preload.php')) {
            if (\file_exists(__DIR__ . '/../../../vendor')) {
                require_once __DIR__ . '/../../../preload.php';
                // test case in rector split package
            } elseif (\file_exists(__DIR__ . '/../../../../../../vendor')) {
                require_once __DIR__ . '/../../../preload-split-package.php';
            }
        }
        if (\file_exists(__DIR__ . '/../../../vendor/scoper-autoload.php')) {
            require_once __DIR__ . '/../../../vendor/scoper-autoload.php';
        }
    }
    private function doTestFileMatchesExpectedContent(string $originalFilePath, string $expectedFilePath, string $fixtureFilePath) : void
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFilePath]);
        $changedContent = $this->processFilePath($originalFilePath);
        // file is removed, we cannot compare it
        if ($this->removedAndAddedFilesCollector->isFileRemoved($originalFilePath)) {
            return;
        }
        try {
            $this->assertStringEqualsFile($expectedFilePath, $changedContent);
        } catch (ExpectationFailedException $exception) {
            FixtureFileUpdater::updateFixtureContent($originalFilePath, $changedContent, $fixtureFilePath);
            $contents = $this->resolveExpectedContents($expectedFilePath);
            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent);
        }
    }
    private function processFilePath(string $filePath) : string
    {
        $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        // needed for PHPStan, because the analyzed file is just created in /temp - need for trait and similar deps
        /** @var NodeScopeResolver $nodeScopeResolver */
        $nodeScopeResolver = $this->getService(NodeScopeResolver::class);
        $nodeScopeResolver->setAnalysedFiles([$filePath]);
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = $this->getService(ConfigurationFactory::class);
        $configuration = $configurationFactory->createForTests([$filePath]);
        $file = new File($filePath, FileSystem::read($filePath));
        $this->applicationFileProcessor->processFiles([$file], $configuration);
        return $file->getFileContent();
    }
}
