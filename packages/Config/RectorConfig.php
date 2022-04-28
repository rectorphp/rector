<?php

declare(strict_types=1);

namespace Rector\Config;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\PhpVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Webmozart\Assert\Assert;

/**
 * @api
 * Same as Symfony container configurator, with patched return type for "set()" method for easier DX.
 * It is an alias for internal class that is prefixed during build, so it's basically for keeping stable public API.
 */
final class RectorConfig extends ContainerConfigurator
{
    /**
     * @param string[] $paths
     */
    public function paths(array $paths): void
    {
        Assert::allString($paths);

        $parameters = $this->parameters();
        $parameters->set(Option::PATHS, $paths);
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
    }

    public function disableParallel(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::PARALLEL, false);
    }

    public function parallel(int $seconds = 120, int $maxNumberOfProcess = 16, int $jobSize = 20): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::PARALLEL, true);

        $parameters->set(Option::PARALLEL_TIMEOUT_IN_SECONDS, $seconds);
        $parameters->set(Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, $maxNumberOfProcess);
        $parameters->set(Option::PARALLEL_JOB_SIZE, $jobSize);
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function skip(array $criteria): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::SKIP, $criteria);
    }

    public function importNames(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    }

    public function importShortClasses(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::IMPORT_SHORT_CLASSES, true);
    }

    public function disableImportShortClasses(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    }

    public function disableImportNames(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::AUTO_IMPORT_NAMES, false);
    }

    /**
     * Set PHPStan custom config to load extensions and custom configuration to Rector.
     * By default, the "phpstan.neon" path is used.
     */
    public function phpstanConfig(string $filePath): void
    {
        Assert::fileExists($filePath);

        $parameters = $this->parameters();
        $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, $filePath);
    }

    /**
     * @param class-string<ConfigurableRectorInterface&RectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $rectorClass, array $configuration): void
    {
        Assert::isAOf($rectorClass, RectorInterface::class);
        Assert::isAOf($rectorClass, ConfigurableRectorInterface::class);

        $services = $this->services();

        $services->set($rectorClass)
            ->configure($configuration);
    }

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function rule(string $rectorClass): void
    {
        Assert::isAOf($rectorClass, RectorInterface::class);

        $services = $this->services();
        $services->set($rectorClass);
    }

    /**
     * @param PhpVersion::* $phpVersion
     */
    public function phpVersion(int $phpVersion): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::PHP_VERSION_FEATURES, $phpVersion);
    }

    /**
     * @param string[] $autoloadPaths
     */
    public function autoloadPaths(array $autoloadPaths): void
    {
        Assert::allString($autoloadPaths);

        $parameters = $this->parameters();
        $parameters->set(Option::AUTOLOAD_PATHS, $autoloadPaths);
    }

    /**
     * @param string[] $bootstrapFiles
     */
    public function bootstrapFiles(array $bootstrapFiles): void
    {
        Assert::allString($bootstrapFiles);

        $parameters = $this->parameters();
        $parameters->set(Option::BOOTSTRAP_FILES, $bootstrapFiles);
    }

    public function symfonyContainerXml(string $filePath): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, $filePath);
    }
}
