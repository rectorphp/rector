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
     * @param mixed[] $paths
     */
    public function paths(array $paths): void
    {
        Assert::allString($paths);

        $parameters = $this->parameters();
        $parameters->set(Option::PATHS, $paths);
    }
}
