<?php declare(strict_types=1);

use Rector\Composer\ComposerModifier;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ComposerModifier::class)
        ->call('setFilePath', [__DIR__ . '/../Fixture/composer_before.json']);
};
