<?php

declare(strict_types=1);

use Rector\Symfony5\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReflectionExtractorEnableMagicCallExtractorRector::class);
};
