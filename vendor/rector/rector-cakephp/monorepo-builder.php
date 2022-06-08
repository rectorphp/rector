<?php

declare (strict_types=1);
namespace RectorPrefix20220608;

use RectorPrefix20220608\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix20220608\Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use RectorPrefix20220608\Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    // @see https://github.com/symplify/monorepo-builder#6-release-flow
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
};
