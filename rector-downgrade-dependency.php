<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::EXCLUDE_PATHS, [
        '/tests/',
        // Individual classes that can be excluded because
        // they are not used by Rector,
        // and they produce some error
        __DIR__ . '/vendor/symfony/cache/DoctrineProvider.php',
    ]);
};
