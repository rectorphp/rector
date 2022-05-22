<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * Rector native configuration provider, to keep deprecated options hidden,
 * but also provide configuration that custom rules can check
 */
final class RectorConfigProvider
{
    public function __construct(
        private readonly ParameterProvider $parameterProvider
    ) {
    }

    public function shouldImportNames(): bool
    {
        return $this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES);
    }

    public function getSymfonyContainerPhp(): string
    {
        return $this->parameterProvider->provideStringParameter(Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER);
    }

    public function getSymfonyContainerXml(): string
    {
        return $this->parameterProvider->provideStringParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
    }
}
