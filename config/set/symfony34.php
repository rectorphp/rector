<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE => [
                'Symfony\Component\Yaml\Yaml' => [
                    'parse' => [
                        2 => ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS'],
                    ],
                ],
            ],
        ]]);

    $services->set(MergeMethodAnnotationToRouteAnnotationRector::class);
};
