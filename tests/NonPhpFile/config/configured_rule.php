<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\Tests\NonPhpFile\Source\TextNonPhpFileProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(TextNonPhpFileProcessor::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__ . '/../Fixture/']);
};
