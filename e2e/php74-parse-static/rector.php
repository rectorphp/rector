<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

// note: ContainerConfigurator on purpose to test older config behavior
// in the future this class will be isolated and completely removed from Rector config
return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/tests',
    ]);

    $services = $containerConfigurator->services();
    $services->set(DowngradeJsonDecodeNullAssociativeArgRector::class);
};
