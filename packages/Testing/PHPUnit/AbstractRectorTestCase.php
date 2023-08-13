<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use Iterator;
use RectorPrefix202308\Nette\Utils\FileSystem;
use RectorPrefix202308\Nette\Utils\Strings;
use PHPUnit\Framework\ExpectationFailedException;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Autoloading\BootstrapFilesIncluder;
use Rector\Core\Configuration\ConfigurationFactory;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\Fixture\FixtureFileFinder;
use Rector\Testing\Fixture\FixtureFileUpdater;
use Rector\Testing\Fixture\FixtureSplitter;
abstract class AbstractRectorTestCase extends \Rector\Testing\PHPUnit\AbstractTestCase implements RectorTestInterface
{
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
    /**
     * @var array<string, true>
     */
    private static $cacheByRuleAndConfig = [];
    protected function setUp() : void
    {
        @\ini_set('memory_limit', '-1');
        $configFile = $this->provideConfigFilePath();
        // boot once for config + test case to avoid booting again and again for every test fixture
        $cacheKey = \sha1($configFile . static::class);
        if (!isset(self::$cacheByRuleAndConfig[$cacheKey])) {
            $this->includePreloadFilesAndScoperAutoload();
            $this->bootFromConfigFiles([$configFile]);
            /** @var AdditionalAutoloader $additionalAutoloader */
            $additionalAutoloader = $this->getService(AdditionalAutoloader::class);
            $additionalAutoloader->autoloadPaths();
            /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
            $bootstrapFilesIncluder = $this->getService(BootstrapFilesIncluder::class);
            $bootstrapFilesIncluder->includeBootstrapFiles();
            self::$cacheByRuleAndConfig[$cacheKey] = \true;
        }
        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->dynamicSourceLocatorProvider = $this->getService(DynamicSourceLocatorProvider::class);
    }
    protected function tearDown() : void
    {
        // clear temporary file
        if (\is_string($this->inputFilePath)) {
            FileSystem::delete($this->inputFilePath);
        }
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
            [$inputFileContents, $expectedFileContents] = FixtureSplitter::splitFixtureFileContents($fixtureFileContents);
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
        SimpleParameterProvider::setParameter(Option::SOURCE, [$originalFilePath]);
        $changedContent = $this->processFilePath($originalFilePath);
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
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = $this->getService(ConfigurationFactory::class);
        $configuration = $configurationFactory->createForTests([$filePath]);
        $this->applicationFileProcessor->processFiles([$filePath], $configuration);
        return FileSystem::read($filePath);
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
