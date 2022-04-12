<?php

declare (strict_types=1);
namespace Rector\Config;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220412\Webmozart\Assert\Assert;
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
        \RectorPrefix20220412\Webmozart\Assert\Assert::allString($paths);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PATHS, $paths);
    }
    /**
     * @param string[] $sets
     */
    public function sets(array $sets) : void
    {
        \RectorPrefix20220412\Webmozart\Assert\Assert::allString($sets);
        foreach ($sets as $set) {
            \RectorPrefix20220412\Webmozart\Assert\Assert::fileExists($set);
            $this->import($set);
        }
    }
    public function parallel() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PARALLEL, \true);
    }
    /**
     * @param array<int|string, mixed> $criteria
     */
    public function skip(array $criteria) : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::SKIP, $criteria);
    }
    public function autoImportNames() : void
    {
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \true);
    }
    /**
     * Set PHPStan custom config to load extensions and custom configuration to Rector.
     * By default, the "phpstan.neon" path is used.
     */
    public function phpstanConfig(string $filePath) : void
    {
        \RectorPrefix20220412\Webmozart\Assert\Assert::fileExists($filePath);
        $parameters = $this->parameters();
        $parameters->set(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH, $filePath);
    }
    /**
     * @param class-string<ConfigurableRectorInterface&RectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function ruleWithConfiguration(string $rectorClass, array $configuration) : void
    {
        \RectorPrefix20220412\Webmozart\Assert\Assert::isAOf($rectorClass, \Rector\Core\Contract\Rector\RectorInterface::class);
        \RectorPrefix20220412\Webmozart\Assert\Assert::isAOf($rectorClass, \Rector\Core\Contract\Rector\ConfigurableRectorInterface::class);
        $services = $this->services();
        $services->set($rectorClass)->configure($configuration);
    }
}
