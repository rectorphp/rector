<?php

declare (strict_types=1);
namespace Rector\Core\Configuration;

use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
/**
 * Rector native configuration provider, to keep deprecated options hidden,
 * but also provide configuration that custom rules can check
 *
 * @api
 * @deprecated Use @see SimpleParametersProvider directly
 */
final class RectorConfigProvider
{
    /**
     * @api symfony
     */
    public function getSymfonyContainerPhp() : string
    {
        return SimpleParameterProvider::provideStringParameter(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER);
    }
    /**
     * @api symfony
     */
    public function getSymfonyContainerXml() : string
    {
        return SimpleParameterProvider::provideStringParameter(\Rector\Core\Configuration\Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
    }
}
