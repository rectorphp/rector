<?php

declare(strict_types=1);

namespace Rector\Config;

use Rector\Core\Configuration\Option;
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
}
