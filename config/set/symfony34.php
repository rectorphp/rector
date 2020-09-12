<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\ValueObject\ArgumentRemover;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([
                new ArgumentRemover(
                    'Symfony\Component\Yaml\Yaml',
                    'parse',
                    2,
                    ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS']
                ),
            ]),
        ]]);

    $services->set(MergeMethodAnnotationToRouteAnnotationRector::class);
};
