<?php

declare (strict_types=1);
namespace Rector\Config;

use RectorPrefix202308\Illuminate\Container\Container;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Configuration\ValueObjectInliner;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\PhpVersion;
use RectorPrefix202308\Webmozart\Assert\Assert;
/**
 * @api soon be used as public API
 */
final class LazyRectorConfig extends Container
{
    /**
     * @param string[] $paths
     */
    public function paths(array $paths) : void
    {
        Assert::allString($paths);
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
    }
    public function disableParallel() : void
    {
        SimpleParameterProvider::setParameter(Option::PARALLEL, \false);
    }
    public function parallel(int $seconds = 120, int $maxNumberOfProcess = 16, int $jobSize = 20) : void
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
     * Set PHPStan custom config to load extensions and custom configuration to Rector.
     * By default, the "phpstan.neon" path is used.
     */
    public function phpstanConfig(string $filePath) : void
    {
        Assert::fileExists($filePath);
        SimpleParameterProvider::setParameter(Option::PHPSTAN_FOR_RECTOR_PATH, $filePath);
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
        // decorate with value object inliner so Symfony understands, see https://getrector.com/blog/2020/09/07/how-to-inline-value-object-in-symfony-php-config
        \array_walk_recursive($configuration, static function (&$value) {
            if (\is_object($value)) {
                $value = ValueObjectInliner::inline($value);
            }
            return $value;
        });
        $this->singleton($rectorClass);
        $this->extend($rectorClass, static function (ConfigurableRectorInterface $configurableRector) use($configuration) : void {
            $configurableRector->configure($configuration);
        });
        $this->tagRectorService($rectorClass);
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function rule(string $rectorClass) : void
    {
        Assert::classExists($rectorClass);
        Assert::isAOf($rectorClass, RectorInterface::class);
        $this->singleton($rectorClass);
        $this->tagRectorService($rectorClass);
    }
    public function import(string $setFilePath) : void
    {
        $self = $this;
        $callable = (require $setFilePath);
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
        $duplicatedRectorClasses = $this->resolveDuplicatedValues($rectorClasses);
        if ($duplicatedRectorClasses !== []) {
            throw new ShouldNotHappenException('Following rules are registered twice: ' . \implode(', ', $duplicatedRectorClasses));
        }
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
     * @param class-string<RectorInterface|PhpRectorInterface> $rectorClass
     */
    private function tagRectorService(string $rectorClass) : void
    {
        $this->tag($rectorClass, RectorInterface::class);
        if (\is_a($rectorClass, PhpRectorInterface::class, \true)) {
            $this->tag($rectorClass, PhpRectorInterface::class);
        } elseif (\is_a($rectorClass, NonPhpRectorInterface::class, \true)) {
            \trigger_error(\sprintf('The "%s" interface of "%s" rule is deprecated. Rector will only PHP code, as designed to with AST. For another file format, use custom tooling.', NonPhpRectorInterface::class, $rectorClass));
            exit;
        }
    }
}
