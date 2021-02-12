<?php

declare(strict_types=1);

use Rector\CodingStyle\Tests\Rector\Use_\UseFunction\Source\UseFunctionRector;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(UseFunctionRector::class);
};
