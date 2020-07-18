<?php

declare(strict_types=1);

use Rector\Core\Rector\Argument\ArgumentDefaultValueReplacerRector;
use Rector\Symfony\Rector\Yaml\ParseFileRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParseFileRector::class);

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->arg('$replacesByMethodAndTypes', [
            'Symfony\Component\Routing\Generator\UrlGeneratorInterface' => [
                'generate' => [
                    2 => [
                        [
                            #  UrlGenerator constants change
                            # https://github.com/symfony/symfony/commit/912fc4de8fd6de1e5397be4a94d39091423e5188
                            'before' => true,
                            'after' => 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL',
                        ], [
                            'before' => false,
                            'after' => 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH',
                        ], [
                            'before' => 'relative',
                            'after' => 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::RELATIVE_PATH',
                        ], [
                            'before' => 'network',
                            'after' => 'Symfony\Component\Routing\Generator\UrlGeneratorInterface::NETWORK_PATH',
                        ],
                    ],
                ],
            ],
        ]);
};
