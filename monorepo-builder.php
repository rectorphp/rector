<?php

// @see https://github.com/symplify/monorepo-builder

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // release workers - in order to execute
    $services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker::class);
    $services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        __DIR__ . '/packages/symfony-php-config' => 'git@github.com:rectorphp/symfony-php-config.git',
        __DIR__ . '/packages/simple-php-doc-parser' => 'git@github.com:rectorphp/simple-php-doc-parser.git',
    ]);
};
