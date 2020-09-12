<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Rector\Symfony\Rector\StaticCall\ParseFileRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ParseFileRector::class);

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => inline_value_objects([
                // https://github.com/symfony/symfony/commit/912fc4de8fd6de1e5397be4a94d39091423e5188
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
                    'generate',
                    2,
                    true,
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL'
                ),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
                    'generate',
                    2,
                    false,
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH'
                ),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
                    'generate',
                    2,
                    'relative',
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface::RELATIVE_PATH'
                ),
                new ArgumentDefaultValueReplacer(
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
                    'generate',
                    2,
                    'network',
                    'Symfony\Component\Routing\Generator\UrlGeneratorInterface::NETWORK_PATH'
                ),
            ]),
        ]]);
};
