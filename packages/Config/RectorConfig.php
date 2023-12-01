<?php

declare (strict_types=1);
namespace Rector\Config;

use RectorPrefix202312\Illuminate\Container\Container;
use PHPStan\Collectors\Collector;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Contract\Rector\CollectorRectorInterface;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\DependencyInjection\Laravel\ContainerMemento;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use RectorPrefix202312\Webmozart\Assert\Assert;
/**
 * @api
 */
final class RectorConfig extends Container
{
    /**
     * @var array<class-string<RectorInterface>, mixed[]>>
     */
    private $ruleConfigurations = [];
    /**
     * @param string[] $paths
     */
    public function paths(array $paths) : void
    {
        Assert::allString($paths);
        foreach ($paths as $path) {
            if (\strpos($path, '*') !== \false) {
                continue;
            }
            Assert::fileExists($path);
        }
        SimpleParameterProvider::setParameter(Option::PATHS, $paths);
    }
    /**
     * @param string[] $sets
     */
    public function sets(array $sets) : void
    {
        Assert::allString($sets);
        foreach ($sets as $set) {
            Assert::fileExists($set);
            $this->import($set);
        }
        // for cache invalidation in case of sets change
        SimpleParameterProvider::addParameter(Option::REGISTERED_RECTOR_SETS, $sets);
    }
    public function disableParallel() : void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, \false);
    }
    /**
     * @experimental since Rector 0.18.x
     */
    public function enableCollectors() : void
    {
        SimpleParameterProvider::setParameter(Option::COLLECTORS, \true);
    }
    public function parallel(int $seconds = 120, int $maxNumberOfProcess = 16, int $jobSize = 15) : void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, \true);
        SimpleParameterProvider::setParameter(Option::PARALLEL_JOB_TIMEOUT_IN_SECONDS, $seconds);
        SimpleParameterProvider::setParameter(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, $maxNumberOfProcess);
        SimpleParameterProvider::setParameter(Option::PARALLEL_JOB_SIZE, $jobSize);
    }
    public function noDiffs() : void
    {
        SimpleParameterProvider::setParameter(Option::NO_DIFFS, \true);
    }
    public function memoryLimit(string $memoryLimit) : void
    {
        SimpleParameterProvider::setParameter(Option::MEMORY_LIMIT, $memoryLimit);
    }
    /**
     * @param array<int|string, mixed> $criteria
     */
    public function skip(array $criteria) : void
    {
        $notExistsRules = [];
        foreach ($criteria as $key => $value) {
            /**
             * Cover define rule then list of files
             *
             * $rectorConfig->skip([
             *      RenameVariableToMatchMethodCallReturnTypeRector::class => [
             *          __DIR__ . '/packages/Config/RectorConfig.php'
             *      ],
             * ]);
             */
            if ($this->isRuleNoLongerExists($key)) {
                $notExistsRules[] = $key;
            }
            if (!\is_string($value)) {
                continue;
            }
            /**
             * Cover direct value without array list of files, eg:
             *
             * $rectorConfig->skip([
             *      StringClassNameToClassConstantRector::class,
             * ]);
             */
            if ($this->isRuleNoLongerExists($value)) {
                $notExistsRules[] = $value;
            }
        }
        if ($notExistsRules !== []) {
            throw new ShouldNotHappenException('Following rules on $rectorConfig->skip() do no longer exist or changed to different namespace: ' . \implode(', ', $notExistsRules));
        }
        SimpleParameterProvider::addParameter(Option::SKIP, $criteria);
    }
    public function removeUnusedImports(bool $removeUnusedImports = \true) : void
    {
        SimpleParameterProvider::setParameter(Option::REMOVE_UNUSED_IMPORTS, $removeUnusedImports);
    }
    public function importNames(bool $importNames = \true, bool $importDocBlockNames = \true) : void
    {
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_NAMES, $importNames);
        SimpleParameterProvider::setParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES, $importDocBlockNames);
    }
    public function importShortClasses(bool $importShortClasses = \true) : void
    {
        SimpleParameterProvider::setParameter(Option::IMPORT_SHORT_CLASSES, $importShortClasses);
    }
    /**
     * Add PHPStan custom config to load extensions and custom configuration to Rector.
     */
    public function phpstanConfig(string $filePath) : void
    {
        Assert::fileExists($filePath);
        SimpleParameterProvider::addParameter(Option::PHPSTAN_FOR_RECTOR_PATHS, [$filePath]);
    }
    /**
     * Add PHPStan custom configs to load extensions and custom configuration to Rector.
     *
     * @param string[] $filePaths
     */
    public function phpstanConfigs(array $filePaths) : void
    {
        Assert::allString($filePaths);
        Assert::allFileExists($filePaths);
        SimpleParameterProvider::addParameter(Option::PHPSTAN_FOR_RECTOR_PATHS, $filePaths);
    }
    /**
     * @param class-string<ConfigurableRectorInterface&RectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $rectorClass, array $configuration) : void
    {
        Assert::classExists($rectorClass);
        Assert::isAOf($rectorClass, RectorInterface::class);
        Assert::isAOf($rectorClass, ConfigurableRectorInterface::class);
        // store configuration to cache
        $this->ruleConfigurations[$rectorClass] = \array_merge($this->ruleConfigurations[$rectorClass] ?? [], $configuration);
        $this->singleton($rectorClass);
        $this->tag($rectorClass, RectorInterface::class);
        $this->afterResolving($rectorClass, function (ConfigurableRectorInterface $configurableRector) use($rectorClass) : void {
            $ruleConfiguration = $this->ruleConfigurations[$rectorClass];
            $configurableRector->configure($ruleConfiguration);
        });
        // for cache invalidation in case of sets change
        SimpleParameterProvider::addParameter(Option::REGISTERED_RECTOR_RULES, $rectorClass);
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function rule(string $rectorClass) : void
    {
        Assert::classExists($rectorClass);
        Assert::isAOf($rectorClass, RectorInterface::class);
        $this->singleton($rectorClass);
        $this->tag($rectorClass, RectorInterface::class);
        if (\is_a($rectorClass, CollectorRectorInterface::class, \true)) {
            $this->tag($rectorClass, CollectorRectorInterface::class);
        }
        // for cache invalidation in case of change
        SimpleParameterProvider::addParameter(Option::REGISTERED_RECTOR_RULES, $rectorClass);
    }
    /**
     * @param class-string<Collector> $collectorClass
     */
    public function collector(string $collectorClass) : void
    {
        $this->singleton($collectorClass);
        $this->tag($collectorClass, Collector::class);
    }
    public function import(string $filePath) : void
    {
        if (\strpos($filePath, '*') !== \false) {
            throw new ShouldNotHappenException('Matching file paths by using glob-patterns is no longer supported. Use specific file path instead.');
        }
        Assert::fileExists($filePath);
        $self = $this;
        $callable = (require $filePath);
        Assert::isCallable($callable);
        /** @var callable(Container $container): void $callable */
        $callable($self);
    }
    /**
     * @param array<class-string<RectorInterface>> $rectorClasses
     */
    public function rules(array $rectorClasses) : void
    {
        Assert::allString($rectorClasses);
        $this->ensureNotDuplicatedClasses($rectorClasses);
        foreach ($rectorClasses as $rectorClass) {
            $this->rule($rectorClass);
        }
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    public function phpVersion(int $phpVersion) : void
    {
        SimpleParameterProvider::setParameter(Option::PHP_VERSION_FEATURES, $phpVersion);
    }
    /**
     * @param string[] $autoloadPaths
     */
    public function autoloadPaths(array $autoloadPaths) : void
    {
        Assert::allString($autoloadPaths);
        SimpleParameterProvider::setParameter(Option::AUTOLOAD_PATHS, $autoloadPaths);
    }
    /**
     * @param string[] $bootstrapFiles
     */
    public function bootstrapFiles(array $bootstrapFiles) : void
    {
        Assert::allString($bootstrapFiles);
        SimpleParameterProvider::setParameter(Option::BOOTSTRAP_FILES, $bootstrapFiles);
    }
    public function symfonyContainerXml(string $filePath) : void
    {
        SimpleParameterProvider::setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, $filePath);
    }
    public function symfonyContainerPhp(string $filePath) : void
    {
        SimpleParameterProvider::setParameter(Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER, $filePath);
    }
    /**
     * @param string[] $extensions
     */
    public function fileExtensions(array $extensions) : void
    {
        Assert::allString($extensions);
        SimpleParameterProvider::setParameter(Option::FILE_EXTENSIONS, $extensions);
    }
    public function cacheDirectory(string $directoryPath) : void
    {
        // cache directory path is created via mkdir in CacheFactory
        // when not exists, so no need to validate $directoryPath is a directory
        SimpleParameterProvider::setParameter(Option::CACHE_DIR, $directoryPath);
    }
    public function containerCacheDirectory(string $directoryPath) : void
    {
        // container cache directory path must be a directory on the first place
        Assert::directory($directoryPath);
        SimpleParameterProvider::setParameter(Option::CONTAINER_CACHE_DIRECTORY, $directoryPath);
    }
    /**
     * @param class-string<CacheStorageInterface> $cacheClass
     */
    public function cacheClass(string $cacheClass) : void
    {
        Assert::isAOf($cacheClass, CacheStorageInterface::class);
        SimpleParameterProvider::setParameter(Option::CACHE_CLASS, $cacheClass);
    }
    /**
     * @see https://github.com/nikic/PHP-Parser/issues/723#issuecomment-712401963
     */
    public function indent(string $character, int $count) : void
    {
        SimpleParameterProvider::setParameter(Option::INDENT_CHAR, $character);
        SimpleParameterProvider::setParameter(Option::INDENT_SIZE, $count);
    }
    /**
     * @api deprecated, just for BC layer warning
     */
    public function services() : void
    {
        \trigger_error('The services() method is deprecated. Use $rectorConfig->singleton(ServiceType::class) instead', \E_USER_ERROR);
    }
    public function resetRuleConfigurations() : void
    {
        $this->ruleConfigurations = [];
    }
    /**
     * Compiler passes-like method
     */
    public function boot() : void
    {
        $skippedClassResolver = new SkippedClassResolver();
        $skippedElements = $skippedClassResolver->resolve();
        foreach ($skippedElements as $skippedClass => $path) {
            if ($path !== null) {
                continue;
            }
            // completely forget the Rector rule only when no path specified
            ContainerMemento::forgetService($this, $skippedClass);
        }
    }
    /**
     * @experimental since Rector 0.18.x
     */
    public function disableCollectors() : void
    {
        SimpleParameterProvider::setParameter(Option::COLLECTORS, \false);
    }
    /**
     * @param mixed $skipRule
     */
    private function isRuleNoLongerExists($skipRule) : bool
    {
        return \is_string($skipRule) && \strpos($skipRule, '*') === \false && \substr_compare($skipRule, 'Rector', -\strlen('Rector')) === 0 && !\is_dir($skipRule) && !\is_file($skipRule) && !\class_exists($skipRule);
    }
    /**
     * @param string[] $values
     * @return string[]
     */
    private function resolveDuplicatedValues(array $values) : array
    {
        $counted = \array_count_values($values);
        $duplicates = [];
        foreach ($counted as $value => $count) {
            if ($count > 1) {
                $duplicates[] = $value;
            }
        }
        return \array_unique($duplicates);
    }
    /**
     * @param string[] $rectorClasses
     */
    private function ensureNotDuplicatedClasses(array $rectorClasses) : void
    {
        $duplicatedRectorClasses = $this->resolveDuplicatedValues($rectorClasses);
        if ($duplicatedRectorClasses === []) {
            return;
        }
        throw new ShouldNotHappenException('Following rules are registered twice: ' . \implode(', ', $duplicatedRectorClasses));
    }
}
