<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use Iterator;
use RectorPrefix202304\Nette\Utils\FileSystem;
use RectorPrefix202304\Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use RectorPrefix202304\Psr\Container\ContainerInterface;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\ParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\Fixture\FixtureFileFinder;
use Rector\Testing\Fixture\FixtureFileUpdater;
use Rector\Testing\Fixture\FixtureSplitter;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
abstract class AbstractRectorTestCase extends \Rector\Testing\PHPUnit\AbstractTestCase implements RectorTestInterface
{
    use MovingFilesTrait;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;
    /**
     * @var \Psr\Container\ContainerInterface|null
     */
    protected static $allRectorContainer;
    /**
     * @var \Rector\Core\Configuration\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    /**
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @var string|null
     */
    private $inputFilePath;
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
        $bootstrapFilesIncluder->includePHPStanExtensionsBoostrapFiles();
    }
    protected function tearDown() : void
    {
        // clear temporary file
        if (\is_string($this->inputFilePath)) {
            FileSystem::delete($this->inputFilePath);
        }
        // free memory and trigger gc to reduce memory peak consumption on windows
        unset($this->applicationFileProcessor, $this->parameterProvider, $this->dynamicSourceLocatorProvider, $this->removedAndAddedFilesCollector);
        \gc_collect_cycles();
    }
    /**
     * @return Iterator<<string>>
     */
    protected static function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        return FixtureFileFinder::yieldDirectory($directory, $suffix);
    }
    protected function isWindows() : bool
    {
        return \strncasecmp(\PHP_OS, 'WIN', 3) === 0;
    }
    protected function doTestFile(string $fixtureFilePath) : void
    {
        // prepare input file contents and expected file output contents
        $fixtureFileContents = FileSystem::read($fixtureFilePath);
        if (FixtureSplitter::containsSplit($fixtureFileContents)) {
            // changed content
            [$inputFileContents, $expectedFileContents] = FixtureSplitter::split($fixtureFilePath);
        } else {
            // no change
            $inputFileContents = $fixtureFileContents;
            $expectedFileContents = $fixtureFileContents;
        }
        $inputFilePath = $this->createInputFilePath($fixtureFilePath);
        // to remove later in tearDown()
        $this->inputFilePath = $inputFilePath;
        if ($fixtureFilePath === $inputFilePath) {
            throw new ShouldNotHappenException('Fixture file and input file cannot be the same: ' . $fixtureFilePath);
        }
        // write temp file
        FileSystem::write($inputFilePath, $inputFileContents);
        $this->doTestFileMatchesExpectedContent($inputFilePath, $expectedFileContents, $fixtureFilePath);
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
    private function doTestFileMatchesExpectedContent(string $originalFilePath, string $expectedFileContents, string $fixtureFilePath) : void
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFilePath]);
        $changedContent = $this->processFilePath($originalFilePath);
        // file is removed, we cannot compare it
        if ($this->removedAndAddedFilesCollector->isFileRemoved($originalFilePath)) {
            return;
        }
        $fixtureFilename = \basename($fixtureFilePath);
        $failureMessage = \sprintf('Failed on fixture file "%s"', $fixtureFilename);
        try {
            $this->assertSame($expectedFileContents, $changedContent, $failureMessage);
        } catch (ExpectationFailedException $exception) {
            FixtureFileUpdater::updateFixtureContent($originalFilePath, $changedContent, $fixtureFilePath);
            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($expectedFileContents, $changedContent, $failureMessage);
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
    private function createInputFilePath(string $fixtureFilePath) : string
    {
        $inputFileDirectory = \dirname($fixtureFilePath);
        // remove ".inc" suffix
        if (\substr_compare($fixtureFilePath, '.inc', -\strlen('.inc')) === 0) {
            $trimmedFixtureFilePath = Strings::substring($fixtureFilePath, 0, -4);
        } else {
            $trimmedFixtureFilePath = $fixtureFilePath;
        }
        $fixtureBasename = \pathinfo($trimmedFixtureFilePath, \PATHINFO_BASENAME);
        return $inputFileDirectory . '/' . $fixtureBasename;
    }
}
