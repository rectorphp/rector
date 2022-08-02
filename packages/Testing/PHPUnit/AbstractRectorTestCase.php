<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use Iterator;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use RectorPrefix202208\Psr\Container\ContainerInterface;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
use Rector\Testing\PHPUnit\Behavior\MultipleFilesChangedTrait;
use RectorPrefix202208\Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use RectorPrefix202208\Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use RectorPrefix202208\Symplify\EasyTesting\StaticFixtureSplitter;
use RectorPrefix202208\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
abstract class AbstractRectorTestCase extends \Rector\Testing\PHPUnit\AbstractTestCase implements RectorTestInterface
{
    use MovingFilesTrait;
    use MultipleFilesChangedTrait;
    /**
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    protected $parameterProvider;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    protected $originalTempFileInfo;
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
        // include local files
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
        $configFile = $this->provideConfigFilePath();
        $this->bootFromConfigFiles([$configFile]);
        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->dynamicSourceLocatorProvider = $this->getService(DynamicSourceLocatorProvider::class);
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
        unset($this->applicationFileProcessor, $this->parameterProvider, $this->dynamicSourceLocatorProvider, $this->removedAndAddedFilesCollector, $this->originalTempFileInfo);
        \gc_collect_cycles();
    }
    /**
     * @return Iterator<SmartFileInfo>
     */
    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively($directory, $suffix);
    }
    protected function isWindows() : bool
    {
        return \strncasecmp(\PHP_OS, 'WIN', 3) === 0;
    }
    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo, bool $allowMatches = \true) : void
    {
        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos($fixtureFileInfo);
        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();
        $this->originalTempFileInfo = $inputFileInfo;
        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();
        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo, $allowMatches);
    }
    protected function getFixtureTempDirectory() : string
    {
        return \sys_get_temp_dir() . '/_temp_fixture_easy_testing';
    }
    private function doTestFileMatchesExpectedContent(SmartFileInfo $originalFileInfo, SmartFileInfo $expectedFileInfo, SmartFileInfo $fixtureFileInfo, bool $allowMatches = \true) : void
    {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);
        $changedContent = $this->processFileInfo($originalFileInfo);
        // file is removed, we cannot compare it
        if ($this->removedAndAddedFilesCollector->isFileRemoved($originalFileInfo)) {
            return;
        }
        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();
        try {
            $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
        } catch (ExpectationFailedException $expectationFailedException) {
            if (!$allowMatches) {
                throw $expectationFailedException;
            }
            StaticFixtureUpdater::updateFixtureContent($originalFileInfo, $changedContent, $fixtureFileInfo);
            $contents = $expectedFileInfo->getContents();
            // make sure we don't get a diff in which every line is different (because of differences in EOL)
            $contents = $this->normalizeNewlines($contents);
            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent, $relativeFilePathFromCwd);
        }
    }
    private function normalizeNewlines(string $string) : string
    {
        return \str_replace("\r\n", "\n", $string);
    }
    private function processFileInfo(SmartFileInfo $fileInfo) : string
    {
        $this->dynamicSourceLocatorProvider->setFileInfo($fileInfo);
        // needed for PHPStan, because the analyzed file is just created in /temp - need for trait and similar deps
        /** @var NodeScopeResolver $nodeScopeResolver */
        $nodeScopeResolver = $this->getService(NodeScopeResolver::class);
        $nodeScopeResolver->setAnalysedFiles([$fileInfo->getRealPath()]);
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = $this->getService(ConfigurationFactory::class);
        $configuration = $configurationFactory->createForTests([$fileInfo->getRealPath()]);
        $file = new File($fileInfo, $fileInfo->getContents());
        $this->applicationFileProcessor->processFiles([$file], $configuration);
        return $file->getFileContent();
    }
}
