<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix202409\Illuminate\Container\RewindableGenerator;
use Iterator;
use RectorPrefix202409\Nette\Utils\FileSystem;
use RectorPrefix202409\Nette\Utils\Strings;
use PHPUnit\Framework\ExpectationFailedException;
use Rector\Application\ApplicationFileProcessor;
use Rector\Autoloading\AdditionalAutoloader;
use Rector\Autoloading\BootstrapFilesIncluder;
use Rector\Configuration\ConfigurationFactory;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\DependencyInjection\ResetableInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\DependencyInjection\Laravel\ContainerMemento;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\Fixture\FixtureFileFinder;
use Rector\Testing\Fixture\FixtureFileUpdater;
use Rector\Testing\Fixture\FixtureSplitter;
use Rector\Testing\PHPUnit\ValueObject\RectorTestResult;
use Rector\Util\Reflection\PrivatesAccessor;
/**
 * @api used by public
 */
abstract class AbstractRectorTestCase extends \Rector\Testing\PHPUnit\AbstractLazyTestCase implements RectorTestInterface
{
    /**
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    /**
     * @var \Rector\Application\ApplicationFileProcessor
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
    /**
     * Restore default parameters
     */
    public static function tearDownAfterClass() : void
    {
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, \false);
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES, \false);
        SimpleParameterProvider::setParameter(Option::REMOVE_UNUSED_IMPORTS, \false);
        SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, \true);
        SimpleParameterProvider::setParameter(Option::INDENT_CHAR, ' ');
        SimpleParameterProvider::setParameter(Option::INDENT_SIZE, 4);
        SimpleParameterProvider::setParameter(Option::POLYFILL_PACKAGES, []);
        SimpleParameterProvider::setParameter(Option::NEW_LINE_ON_FLUENT_CALL, \false);
    }
    protected function setUp() : void
    {
        $this->includePreloadFilesAndScoperAutoload();
        $configFile = $this->provideConfigFilePath();
        // cleanup all registered rectors, so you can use only the new ones
        $rectorConfig = self::getContainer();
        // boot once for config + test case to avoid booting again and again for every test fixture
        $cacheKey = \sha1($configFile . static::class);
        if (!isset(self::$cacheByRuleAndConfig[$cacheKey])) {
            // reset
            /** @var RewindableGenerator<int, ResetableInterface> $resetables */
            $resetables = $rectorConfig->tagged(ResetableInterface::class);
            foreach ($resetables as $resetable) {
                /** @var ResetableInterface $resetable */
                $resetable->reset();
            }
            $this->forgetRectorsRules();
            $rectorConfig->resetRuleConfigurations();
            // this has to be always empty, so we can add new rules with their configuration
            $this->assertEmpty($rectorConfig->tagged(RectorInterface::class));
            $this->bootFromConfigFiles([$configFile]);
            $rectorsGenerator = $rectorConfig->tagged(RectorInterface::class);
            $rectors = $rectorsGenerator instanceof RewindableGenerator ? \iterator_to_array($rectorsGenerator->getIterator()) : [];
            /** @var RectorNodeTraverser $rectorNodeTraverser */
            $rectorNodeTraverser = $rectorConfig->make(RectorNodeTraverser::class);
            $rectorNodeTraverser->refreshPhpRectors($rectors);
            // store cache
            self::$cacheByRuleAndConfig[$cacheKey] = \true;
        }
        $this->applicationFileProcessor = $this->make(ApplicationFileProcessor::class);
        $this->dynamicSourceLocatorProvider = $this->make(DynamicSourceLocatorProvider::class);
        /** @var AdditionalAutoloader $additionalAutoloader */
        $additionalAutoloader = $this->make(AdditionalAutoloader::class);
        $additionalAutoloader->autoloadPaths();
        /** @var BootstrapFilesIncluder $bootstrapFilesIncluder */
        $bootstrapFilesIncluder = $this->make(BootstrapFilesIncluder::class);
        $bootstrapFilesIncluder->includeBootstrapFiles();
    }
    protected function tearDown() : void
    {
        // clear temporary file
        if (\is_string($this->inputFilePath)) {
            FileSystem::delete($this->inputFilePath);
        }
    }
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
        FileSystem::write($inputFilePath, $inputFileContents, null);
        $this->doTestFileMatchesExpectedContent($inputFilePath, $inputFileContents, $expectedFileContents, $fixtureFilePath);
    }
    private function forgetRectorsRules() : void
    {
        $rectorConfig = self::getContainer();
        // 1. forget tagged services
        ContainerMemento::forgetTag($rectorConfig, RectorInterface::class);
        // 2. remove after binding too, to avoid setting configuration over and over again
        $privatesAccessor = new PrivatesAccessor();
        $privatesAccessor->propertyClosure($rectorConfig, 'afterResolvingCallbacks', static function (array $afterResolvingCallbacks) : array {
            foreach (\array_keys($afterResolvingCallbacks) as $key) {
                if ($key === AbstractRector::class) {
                    continue;
                }
                if (\is_a($key, RectorInterface::class, \true)) {
                    unset($afterResolvingCallbacks[$key]);
                }
            }
            return $afterResolvingCallbacks;
        });
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
    private function doTestFileMatchesExpectedContent(string $originalFilePath, string $inputFileContents, string $expectedFileContents, string $fixtureFilePath) : void
    {
        SimpleParameterProvider::setParameter(Option::SOURCE, [$originalFilePath]);
        // the file is now changed (if any rule matches)
        $rectorTestResult = $this->processFilePath($originalFilePath);
        $changedContents = $rectorTestResult->getChangedContents();
        $fixtureFilename = \basename($fixtureFilePath);
        $failureMessage = \sprintf('Failed on fixture file "%s"', $fixtureFilename);
        // give more context about used rules in case of set testing
        if (\count($rectorTestResult->getAppliedRectorClasses()) > 1) {
            $failureMessage .= \PHP_EOL . \PHP_EOL;
            $failureMessage .= 'Applied Rector rules:' . \PHP_EOL;
            foreach ($rectorTestResult->getAppliedRectorClasses() as $appliedRectorClass) {
                $failureMessage .= ' * ' . $appliedRectorClass . \PHP_EOL;
            }
        }
        try {
            $this->assertSame($expectedFileContents, $changedContents, $failureMessage);
        } catch (ExpectationFailedException $exception) {
            FixtureFileUpdater::updateFixtureContent($inputFileContents, $changedContents, $fixtureFilePath);
            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($expectedFileContents, $changedContents, $failureMessage);
        }
    }
    private function processFilePath(string $filePath) : RectorTestResult
    {
        $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        /** @var ConfigurationFactory $configurationFactory */
        $configurationFactory = $this->make(ConfigurationFactory::class);
        $configuration = $configurationFactory->createForTests([$filePath]);
        $processResult = $this->applicationFileProcessor->processFiles([$filePath], $configuration);
        // return changed file contents
        $changedFileContents = FileSystem::read($filePath);
        return new RectorTestResult($changedFileContents, $processResult);
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
