<?php

declare(strict_types=1);

use Rector\Core\Bootstrap\ExtensionConfigResolver;
use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/services.php');
    $containerConfigurator->import(__DIR__ . '/services-rules.php');
    $containerConfigurator->import(__DIR__ . '/services-packages.php');
    $containerConfigurator->import(__DIR__ . '/parameters.php');

    $extensionConfigResolver = new ExtensionConfigResolver();
    $extensionConfigFiles = $extensionConfigResolver->provide();
    foreach ($extensionConfigFiles as $extensionConfigFile) {
        $containerConfigurator->import($extensionConfigFile->getRealPath());
    }

    // require only in dev
    $containerConfigurator->import(__DIR__ . '/../utils/compiler/config/config.php', null, 'not_found');

    // to override extension-loaded config
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-rector.neon');
};
