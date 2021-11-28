<?php

declare(strict_types=1);

use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FuncCallToNewRector::class)
        ->configure([
            'collection' => 'Collection',
        ]);
};
