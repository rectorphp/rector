<?php

declare(strict_types=1);

namespace Rector\Config;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
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

    public function parallel(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::PARALLEL, true);
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function skip(array $criteria): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::SKIP, $criteria);
    }

    public function autoImportNames(): void
    {
        $parameters = $this->parameters();
        $parameters->set(Option::AUTO_IMPORT_NAMES, true);
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
}
