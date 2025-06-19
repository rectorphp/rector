<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

use RectorPrefix202506\Illuminate\Container\RewindableGenerator;
use Iterator;
use RectorPrefix202506\Nette\Utils\FileSystem;
use RectorPrefix202506\Nette\Utils\Strings;
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
    private DynamicSourceLocatorProvider $dynamicSourceLocatorProvider;
    private ApplicationFileProcessor $applicationFileProcessor;
    private ?string $inputFilePath = null;
    /**
     * @var array<string, true>
     */
    private static array $cacheByRuleAndConfig = [];
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
        SimpleParameterProvider::setParameter(Option::TREAT_CLASSES_AS_FINAL, \false);
    }
    protected function setUp() : void
    {
        parent::setUp();
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
    protected function doTestFile(string $fixtureFilePath, bool $includeFixtureDirectoryAsSource = \false) : void
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
        $this->doTestFileMatchesExpectedContent($inputFilePath, $inputFileContents, $expectedFileContents, $fixtureFilePath, $includeFixtureDirectoryAsSource);
    }
    protected function doTestFileExpectingWarningAboutRuleApplied(string $fixtureFilePath, string $expectedRuleApplied) : void
    {
        \ob_start();
        $this->doTestFile($fixtureFilePath);
        $content = \ob_get_clean();
        $fixtureName = \basename($fixtureFilePath);
        $testClass = static::class;
        $this->assertSame(\PHP_EOL . 'WARNING: On fixture file "' . $fixtureName . '" for test "' . $testClass . '"' . \PHP_EOL . 'File not changed but some Rector rules applied:' . \PHP_EOL . ' * ' . $expectedRuleApplied . \PHP_EOL, $content);
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
    private function doTestFileMatchesExpectedContent(string $originalFilePath, string $inputFileContents, string $expectedFileContents, string $fixtureFilePath, bool $includeFixtureDirectoryAsSource) : void
    {
        SimpleParameterProvider::setParameter(Option::SOURCE, [$originalFilePath]);
        // the file is now changed (if any rule matches)
        $rectorTestResult = $this->processFilePath($originalFilePath, $includeFixtureDirectoryAsSource);
        $changedContents = $rectorTestResult->getChangedContents();
        $fixtureFilename = \basename($fixtureFilePath);
        $failureMessage = \sprintf('Failed on fixture file "%s"', $fixtureFilename);
        $numAppliedRectorClasses = \count($rectorTestResult->getAppliedRectorClasses());
        // give more context about used rules in case of set testing
        $appliedRulesList = '';
        if ($numAppliedRectorClasses > 0) {
            foreach ($rectorTestResult->getAppliedRectorClasses() as $appliedRectorClass) {
                $appliedRulesList .= ' * ' . $appliedRectorClass . \PHP_EOL;
            }
        }
        if ($numAppliedRectorClasses > 1) {
            $failureMessage .= \PHP_EOL . \PHP_EOL . 'Applied Rector rules:' . \PHP_EOL . $appliedRulesList;
        }
        try {
            $this->assertSame($expectedFileContents, $changedContents, $failureMessage);
        } catch (ExpectationFailedException $exception) {
            FixtureFileUpdater::updateFixtureContent($inputFileContents, $changedContents, $fixtureFilePath);
            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($expectedFileContents, $changedContents, $failureMessage);
        }
        if ($inputFileContents === $expectedFileContents && $numAppliedRectorClasses > 0) {
            $failureMessage = \PHP_EOL . \sprintf('WARNING: On fixture file "%s" for test "%s"', $fixtureFilename, static::class) . \PHP_EOL . 'File not changed but some Rector rules applied:' . \PHP_EOL . $appliedRulesList;
            echo $failureMessage;
        }
    }
    private function processFilePath(string $filePath, bool $includeFixtureDirectoryAsSource) : RectorTestResult
    {
        if ($includeFixtureDirectoryAsSource) {
            $fixtureDirectory = \dirname($filePath);
            $this->dynamicSourceLocatorProvider->addDirectories([$fixtureDirectory]);
        } else {
            $this->dynamicSourceLocatorProvider->setFilePath($filePath);
        }
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
