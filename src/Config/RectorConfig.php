<?php

declare (strict_types=1);
namespace Rector\Config;

use RectorPrefix202608\Composer\Semver\Semver;
use Deprecated;
use RectorPrefix202608\Entropy\Container\Container;
use Override;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Composer\InstalledPackageResolver;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Contract\DependencyInjection\RelatedConfigInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Enum\Config\Defaults;
use Rector\Exception\ShouldNotHappenException;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\Validation\RectorConfigValidator;
use Rector\ValueObject\Configuration\LevelOverflow;
use Rector\ValueObject\PhpVersion;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\ValueObject\ComposerBoundRuleConfiguration;
use RectorPrefix202608\Symfony\Component\Console\Command\Command;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api
 * @see \Rector\Tests\Config\RectorConfigTest
 */
final class RectorConfig extends Container
{
    /**
     * @var array<class-string<ConfigurableRectorInterface>, mixed[]>
     */
    private array $ruleConfigurations = [];
    /**
     * @var array<class-string<RectorInterface>, true>
     */
    private array $registeredRectorClasses = [];
    /**
     * @var array<string, true>
     */
    private array $registeredComposerBoundRuleConfigurations = [];
    /**
     * Optional override, e.g. injected by a test to read the versions from a standalone "composer.json"
     */
    private ?InstalledPackageResolver $installedPackageResolver = null;
    /**
     * Explicitly registered service ids, used for bound() and to drive forgetting on skip()/reset.
     *
     * @var array<class-string, true>
     */
    private array $boundAbstracts = [];
    /**
     * Service ids that got a factory closure registered on the entropy container.
     *
     * @var array<class-string, true>
     */
    private array $factoryBound = [];
    private static ?bool $recreated = null;
    /**
     * @internal Resets the root-config detection, so tests that assert on root
     * rule registration behave the same whether run alone or batched into one
     * warm process by a parallel runner.
     */
    public static function resetRecreated(): void
    {
        self::$recreated = null;
    }
    public static function configure(): RectorConfigBuilder
    {
        if (self::$recreated === null) {
            self::$recreated = \false;
        } elseif (self::$recreated === \false) {
            self::$recreated = \true;
        }
        SimpleParameterProvider::setParameter(Option::IS_RECTORCONFIG_BUILDER_RECREATED, self::$recreated);
        return new RectorConfigBuilder();
    }
    /**
     * @param string[] $paths
     */
    public function paths(array $paths): void
    {
        Assert::allString($paths);
        // ensure paths exist
        foreach ($paths as $path) {
            if (strpos($path, '*') !== \false) {
                continue;
            }
            Assert::fileExists($path);
        }
        SimpleParameterProvider::setParameter(Option::PATHS, $paths);
    }
    /**
     * @param string[] $sets
     */
    public function sets(array $sets): void
    {
        Assert::allString($sets);
        foreach ($sets as $set) {
            Assert::fileExists($set);
            $this->import($set);
        }
        // for cache invalidation in case of sets change
        SimpleParameterProvider::addParameter(Option::REGISTERED_RECTOR_SETS, $sets);
    }
    public function disableParallel(): void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, \false);
    }
    public function parallel(int $processTimeout = 120, int $maxNumberOfProcess = Defaults::PARALLEL_MAX_NUMBER_OF_PROCESS, int $jobSize = 16): void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, \true);
        SimpleParameterProvider::setParameter(Option::PARALLEL_JOB_TIMEOUT_IN_SECONDS, $processTimeout);
        SimpleParameterProvider::setParameter(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, $maxNumberOfProcess);
        SimpleParameterProvider::setParameter(Option::PARALLEL_JOB_SIZE, $jobSize);
    }
    public function noDiffs(): void
    {
        SimpleParameterProvider::setParameter(Option::NO_DIFFS, \true);
    }
    public function memoryLimit(string $memoryLimit): void
    {
        SimpleParameterProvider::setParameter(Option::MEMORY_LIMIT, $memoryLimit);
    }
    /**
     * @see https://getrector.com/documentation/ignoring-rules-or-paths
     * @param array<int|string, mixed> $skip
     */
    public function skip(array $skip): void
    {
        RectorConfigValidator::ensureRectorRulesExist($skip);
        SimpleParameterProvider::addParameter(Option::SKIP, $skip);
    }
    public function removeUnusedImports(bool $removeUnusedImports = \true): void
    {
        SimpleParameterProvider::setParameter(Option::REMOVE_UNUSED_IMPORTS, $removeUnusedImports);
    }
    public function importNames(bool $importNames = \true, bool $importDocBlockNames = \true): void
    {
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, $importNames);
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES, $importDocBlockNames);
    }
    public function importShortClasses(bool $importShortClasses = \true): void
    {
        SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, $importShortClasses);
    }
    /**
     * Add PHPStan custom config to load extensions and custom configuration to Rector.
     */
    public function phpstanConfig(string $filePath): void
    {
        Assert::fileExists($filePath);
        SimpleParameterProvider::addParameter(Option::PHPSTAN_FOR_RECTOR_PATHS, [$filePath]);
    }
    /**
     * Add PHPStan custom configs to load extensions and custom configuration to Rector.
     *
     * @param string[] $filePaths
     */
    public function phpstanConfigs(array $filePaths): void
    {
        Assert::allString($filePaths);
        Assert::allFileExists($filePaths);
        SimpleParameterProvider::addParameter(Option::PHPSTAN_FOR_RECTOR_PATHS, $filePaths);
    }
    /**
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $rectorClass, array $configuration): void
    {
        Assert::classExists($rectorClass);
        Assert::isAOf($rectorClass, RectorInterface::class);
        Assert::isAOf($rectorClass, ConfigurableRectorInterface::class);
        // store configuration to cache
        $this->ruleConfigurations[$rectorClass] = array_merge($this->ruleConfigurations[$rectorClass] ?? [], $configuration);
        $this->rule($rectorClass);
        $this->afterResolving($rectorClass, function (ConfigurableRectorInterface $configurableRector) use ($rectorClass): void {
            // the rule may have been re-registered without configuration since this callback was
            // queued (e.g. a later test reusing the rule via a set), so skip when it has no config
            if (!isset($this->ruleConfigurations[$rectorClass])) {
                return;
            }
            $configurableRector->configure($this->ruleConfigurations[$rectorClass]);
        });
    }
    /**
     * Register the rule configuration only if the package version installed in the analysed project satisfies
     * the version constraint. Useful for configuration valid since a specific package version,
     * e.g. an attribute added in PHPUnit 11.
     *
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfigurationComposerVersionBound(string $rectorClass, array $configuration, string $packageName, string $versionConstraint): void
    {
        $packageVersion = $this->resolveInstalledPackageVersion($packageName);
        $isActive = $packageVersion !== null && Semver::satisfies($packageVersion, $versionConstraint);
        // the same rule configuration can be registered by multiple sets, report it only once
        $configurationKey = $rectorClass . '|' . $packageName . '|' . $versionConstraint . '|' . serialize($configuration);
        if (!isset($this->registeredComposerBoundRuleConfigurations[$configurationKey])) {
            $this->registeredComposerBoundRuleConfigurations[$configurationKey] = \true;
            // reported by the "composer-based" command, the inactive ones as well
            SimpleParameterProvider::addParameter(Option::COMPOSER_BOUND_RULE_CONFIGURATIONS, [new ComposerBoundRuleConfiguration($rectorClass, $packageName, $versionConstraint, $configuration, $isActive)]);
        }
        if (!$isActive) {
            return;
        }
        $this->ruleWithConfiguration($rectorClass, $configuration);
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function rule(string $rectorClass): void
    {
        Assert::classExists($rectorClass);
        Assert::isAOf($rectorClass, RectorInterface::class);
        $this->singleton($rectorClass);
        // the same rule can be registered by multiple sets, record it only once,
        // otherwise it is run twice on every node and listed twice in the reports
        if (!isset($this->registeredRectorClasses[$rectorClass])) {
            $this->registeredRectorClasses[$rectorClass] = \true;
            // for cache invalidation in case of change
            SimpleParameterProvider::addParameter(Option::REGISTERED_RECTOR_RULES, $rectorClass);
        }
        if (is_a($rectorClass, RelatedConfigInterface::class, \true)) {
            $configFile = $rectorClass::getConfigFile();
            Assert::file($configFile, sprintf('The config path "%s" in "%s::getConfigFile()" could not be found', $configFile, $rectorClass));
            $this->import($configFile);
        }
    }
    /**
     * @param class-string<Command> $commandClass
     */
    public function command(string $commandClass): void
    {
        $this->singleton($commandClass);
    }
    public function import(string $filePath): void
    {
        /**
         * Only stop when filePath realpath is false and contains glob patterns
         * @see https://github.com/rectorphp/rector/issues/9156#issuecomment-2869130541
         */
        if (realpath($filePath) === \false && strpos($filePath, '*') !== \false) {
            throw new ShouldNotHappenException('Matching file paths by using glob-patterns is no longer supported. Use specific file path instead.');
        }
        Assert::fileExists($filePath);
        $self = $this;
        $callable = require $filePath;
        Assert::isCallable($callable);
        /** @var callable(Container $container): void $callable */
        $callable($self);
    }
    /**
     * @param array<class-string<RectorInterface>> $rectorClasses
     */
    public function rules(array $rectorClasses): void
    {
        Assert::allString($rectorClasses);
        RectorConfigValidator::ensureNoDuplicatedClasses($rectorClasses);
        foreach ($rectorClasses as $rectorClass) {
            $this->rule($rectorClass);
        }
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    public function phpVersion(int $phpVersion): void
    {
        SimpleParameterProvider::setParameter(Option::PHP_VERSION_FEATURES, $phpVersion);
    }
    /**
     * @internal
     *
     * @api only for testing. It is parsed from composer.json "require" packages by default
     * @param array<PolyfillPackage::*> $polyfillPackages
     */
    public function polyfillPackages(array $polyfillPackages): void
    {
        SimpleParameterProvider::setParameter(Option::POLYFILL_PACKAGES, $polyfillPackages);
    }
    /**
     * @param string[] $autoloadPaths
     */
    public function autoloadPaths(array $autoloadPaths): void
    {
        Assert::allString($autoloadPaths);
        SimpleParameterProvider::setParameter(Option::AUTOLOAD_PATHS, $autoloadPaths);
    }
    /**
     * @param string[] $bootstrapFiles
     */
    public function bootstrapFiles(array $bootstrapFiles): void
    {
        Assert::allString($bootstrapFiles);
        SimpleParameterProvider::setParameter(Option::BOOTSTRAP_FILES, $bootstrapFiles);
    }
    public function symfonyContainerXml(string $filePath): void
    {
        SimpleParameterProvider::setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, $filePath);
    }
    public function symfonyContainerPhp(string $filePath): void
    {
        SimpleParameterProvider::setParameter(Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER, $filePath);
    }
    public function newLineOnFluentCall(bool $enabled = \true): void
    {
        SimpleParameterProvider::setParameter(Option::NEW_LINE_ON_FLUENT_CALL, $enabled);
    }
    public function treatClassesAsFinal(bool $treatClassesAsFinal = \true): void
    {
        SimpleParameterProvider::setParameter(Option::TREAT_CLASSES_AS_FINAL, $treatClassesAsFinal);
    }
    /**
     * Guard the listed classes and their descendants against method signature changes that would
     * break child classes - e.g. adding a return type or a param type. Only non-final classes are
     * guarded, as final classes cannot be extended.
     *
     * @param string[] $classes
     */
    public function typeGuardedClasses(array $classes): void
    {
        Assert::allString($classes);
        SimpleParameterProvider::setParameter(Option::TYPE_GUARDED_CLASSES, $classes);
    }
    /**
     * @param string[] $extensions
     */
    public function fileExtensions(array $extensions): void
    {
        Assert::allString($extensions);
        SimpleParameterProvider::setParameter(Option::FILE_EXTENSIONS, $extensions);
    }
    public function cacheDirectory(string $directoryPath): void
    {
        // cache directory path is created via mkdir in CacheFactory
        // when not exists, so no need to validate $directoryPath is a directory
        SimpleParameterProvider::setParameter(Option::CACHE_DIR, $directoryPath);
    }
    public function containerCacheDirectory(string $directoryPath): void
    {
        // container cache directory path must be a directory on the first place
        Assert::directory($directoryPath);
        SimpleParameterProvider::setParameter(Option::CONTAINER_CACHE_DIRECTORY, $directoryPath);
    }
    /**
     * @param class-string<CacheStorageInterface> $cacheClass
     */
    public function cacheClass(string $cacheClass): void
    {
        Assert::isAOf($cacheClass, CacheStorageInterface::class);
    }
    /**
     * @param class-string $cacheMetaExtensionClass
     */
    public function cacheMetaExtension(string $cacheMetaExtensionClass): void
    {
        SimpleParameterProvider::addParameter(Option::CACHE_META_EXTENSIONS, $cacheMetaExtensionClass);
    }
    /**
     * @see https://github.com/nikic/PHP-Parser/issues/723#issuecomment-712401963
     */
    public function indent(string $character, int $count): void
    {
        SimpleParameterProvider::setParameter(Option::INDENT_CHAR, $character);
        SimpleParameterProvider::setParameter(Option::INDENT_SIZE, $count);
    }
    /**
     * @internal
     * @api used only in tests
     */
    public function resetRuleConfigurations(): void
    {
        $this->ruleConfigurations = [];
        $this->registeredRectorClasses = [];
        $this->registeredComposerBoundRuleConfigurations = [];
    }
    /**
     * Compiler passes-like method
     */
    public function boot(): void
    {
        $skippedClassResolver = new SkippedClassResolver();
        $skippedElements = $skippedClassResolver->resolve();
        foreach ($skippedElements as $skippedClass => $path) {
            if ($path !== null) {
                continue;
            }
            // completely forget the Rector rule only when no path specified
            $this->forgetByContract($skippedClass);
        }
    }
    /**
     * Register a shared service. Without a $concrete factory the entropy container autowires the
     * class on demand via reflection; register() makes it discoverable by the interfaces it
     * implements, so findByContract() can find it without any explicit tagging.
     *
     * @param class-string $abstract
     * @param (callable(self): object)|null $concrete
     */
    public function singleton(string $abstract, ?callable $concrete = null): void
    {
        $this->boundAbstracts[$abstract] = \true;
        if ($concrete === null) {
            // no factory: let the entropy container discover it by contract
            $this->register($abstract);
            return;
        }
        if (!isset($this->factoryBound[$abstract])) {
            $this->factoryBound[$abstract] = \true;
            // entropy calls the factory with the container instance, which is always this RectorConfig
            parent::service($abstract, fn(): object => $concrete($this));
        }
    }
    /**
     * PSR-11 style accessor, kept for call sites that read services eagerly.
     *
     * @template TObject of object
     * @param class-string<TObject> $id
     * @return TObject
     */
    public function get(string $id): object
    {
        return $this->make($id);
    }
    /**
     * @param class-string $abstract
     */
    public function bound(string $abstract): bool
    {
        return isset($this->boundAbstracts[$abstract]);
    }
    /**
     * Forget every service of the contract, both from the entropy container and from the local
     * bookkeeping, so a skipped or reset service is not seen as bound and cannot be resurrected
     * through discovery.
     *
     * @param class-string $contract
     */
    #[Override]
    public function forgetByContract(string $contract): void
    {
        parent::forgetByContract($contract);
        foreach (array_keys($this->boundAbstracts) as $abstract) {
            if (!is_a($abstract, $contract, \true)) {
                continue;
            }
            unset($this->boundAbstracts[$abstract], $this->factoryBound[$abstract], $this->registeredRectorClasses[$abstract]);
        }
    }
    public function reportingRealPath(bool $absolute = \true): void
    {
        SimpleParameterProvider::setParameter(Option::ABSOLUTE_FILE_PATH, $absolute);
    }
    public function reportUnusedSkips(bool $report = \true): void
    {
        SimpleParameterProvider::setParameter(Option::REPORT_UNUSED_SKIPS, $report);
    }
    public function editorUrl(string $editorUrl): void
    {
        SimpleParameterProvider::setParameter(Option::EDITOR_URL, $editorUrl);
    }
    /**
     * @internal Used only for bridge
     * @return array<class-string<ConfigurableRectorInterface>, mixed>
     */
    public function getRuleConfigurations(): array
    {
        return $this->ruleConfigurations;
    }
    /**
     * @internal Used only for bridge
     * @return array<class-string<RectorInterface>>
     */
    public function getMainRectorClasses(): array
    {
        return array_keys($this->registeredRectorClasses);
    }
    /**
     * @internal used to report level overflows in configuration
     * @param LevelOverflow[] $levelOverflows
     */
    public function setOverflowLevels(array $levelOverflows): void
    {
        SimpleParameterProvider::addParameter(Option::LEVEL_OVERFLOWS, $levelOverflows);
    }
    private function resolveInstalledPackageVersion(string $packageName): ?string
    {
        // an explicitly injected resolver wins, e.g. a test pointing at a standalone "composer.json"
        if (!$this->installedPackageResolver instanceof InstalledPackageResolver) {
            // otherwise reuse the container-bound resolver, so a test-provided "composer.json" (via
            // AbstractRectorTestCase::provideComposerJsonFilePath()) drives the version, not the project root
            $this->installedPackageResolver = $this->bound(InstalledPackageResolver::class) ? $this->make(InstalledPackageResolver::class) : new InstalledPackageResolver();
        }
        return $this->installedPackageResolver->resolvePackageVersion($packageName);
    }
}
