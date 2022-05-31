<?php

declare (strict_types=1);
namespace Rector\Config;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\ValueObjectInliner;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\PhpVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @api
 * Same as Symfony container configurator, with patched return type for "set()" method for easier DX.
 * It is an alias for internal class that is prefixed during build, so it's basically for keeping stable public API.
 */
final class RectorConfig extends \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator
{
    /**
     * @param string[] $paths
     */
    public function paths(array $paths) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($paths);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PATHS, $paths);
    }
    /**
     * @param string[] $sets
     */
    public function sets(array $sets) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($sets);
        foreach ($sets as $set) {
            \RectorPrefix20220531\Webmozart\Assert\Assert::fileExists($set);
            $this->import($set);
        }
    }
    public function disableParallel() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \false);
    }
    public function parallel(int $seconds = 120, int $maxNumberOfProcess = 16, int $jobSize = 20) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_TIMEOUT_IN_SECONDS, $seconds);
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, $maxNumberOfProcess);
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_JOB_SIZE, $jobSize);
    }
    /**
     * @param array<int|string, mixed> $criteria
     */
    public function skip(array $criteria) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::SKIP, $criteria);
    }
    public function importNames() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    }
    public function importShortClasses() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES, \true);
    }
    public function disableImportShortClasses() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES, \false);
    }
    public function disableImportNames() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \false);
    }
    /**
     * Set PHPStan custom config to load extensions and custom configuration to Rector.
     * By default, the "phpstan.neon" path is used.
     */
    public function phpstanConfig(string $filePath) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::fileExists($filePath);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH, $filePath);
    }
    /**
     * @param class-string<ConfigurableRectorInterface&RectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $rectorClass, array $configuration) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::classExists($rectorClass);
        \RectorPrefix20220531\Webmozart\Assert\Assert::isAOf($rectorClass, \Rector\Core\Contract\Rector\RectorInterface::class);
        \RectorPrefix20220531\Webmozart\Assert\Assert::isAOf($rectorClass, \Rector\Core\Contract\Rector\ConfigurableRectorInterface::class);
        $services = $this->services();
        // decorate with value object inliner so Symfony understands, see https://getrector.org/blog/2020/09/07/how-to-inline-value-object-in-symfony-php-config
        \array_walk_recursive($configuration, function (&$value) {
            if (\is_object($value)) {
                $value = \Rector\Core\Configuration\ValueObjectInliner::inline($value);
            }
            return $value;
        });
        $services->set($rectorClass)->call('configure', [$configuration]);
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function rule(string $rectorClass) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::classExists($rectorClass);
        \RectorPrefix20220531\Webmozart\Assert\Assert::isAOf($rectorClass, \Rector\Core\Contract\Rector\RectorInterface::class);
        $services = $this->services();
        $services->set($rectorClass);
    }
    /**
     * @param array<class-string<RectorInterface>> $rectorClasses
     */
    public function rules(array $rectorClasses) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($rectorClasses);
        foreach ($rectorClasses as $rectorClass) {
            $this->rule($rectorClass);
        }
    }
    /**
     * @param PhpVersion::* $phpVersion
     */
    public function phpVersion(int $phpVersion) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, $phpVersion);
    }
    /**
     * @param string[] $autoloadPaths
     */
    public function autoloadPaths(array $autoloadPaths) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($autoloadPaths);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::AUTOLOAD_PATHS, $autoloadPaths);
    }
    /**
     * @param string[] $bootstrapFiles
     */
    public function bootstrapFiles(array $bootstrapFiles) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($bootstrapFiles);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::BOOTSTRAP_FILES, $bootstrapFiles);
    }
    public function symfonyContainerXml(string $filePath) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, $filePath);
    }
    public function symfonyContainerPhp(string $filePath) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER, $filePath);
    }
    /**
     * @param string[] $extensions
     */
    public function fileExtensions(array $extensions) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($extensions);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::FILE_EXTENSIONS, $extensions);
    }
    public function nestedChainMethodCallLimit(int $limit) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::NESTED_CHAIN_METHOD_CALL_LIMIT, $limit);
    }
    public function cacheDirectory(string $directoryPath) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::CACHE_DIR, $directoryPath);
    }
    /**
     * @param class-string<CacheStorageInterface> $cacheClass
     */
    public function cacheClass(string $cacheClass) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::isAOf($cacheClass, \Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface::class);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::CACHE_CLASS, $cacheClass);
    }
}
