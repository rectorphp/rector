<?php

declare(strict_types=1);

use Rector\Php73\Rector\String_\SensitiveHereNowDocRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SensitiveHereNowDocRector::class);
};
