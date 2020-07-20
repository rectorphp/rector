<?php

declare(strict_types=1);

use Rector\Core\Rector\Argument\ArgumentRemoverRector;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->arg('$positionsByMethodNameByClassType', [
            'Symfony\Component\Yaml\Yaml' => [
                'parse' => [
                    2 => ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS'],
                ],
            ],
        ]);

    $services->set(MergeMethodAnnotationToRouteAnnotationRector::class);
};
