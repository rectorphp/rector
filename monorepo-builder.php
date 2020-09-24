<?php

// @see https://github.com/symplify/monorepo-builder

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // release workers - in order to execute
    $services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker::class);
    $services->set(Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker::class);
};
